#!/usr/bin/perl -w
#
# This Program contacts an authoritative Nameserver to request
# an actual Serial number of an given domain, then it ask an 
# LDAP Server (which is extended by the dnsZone Schema) for 
# the entries of this domain. If the serial in the LDAP Server is newer
# then the zone File will be recreated, otherwise nothing is done! 
#
# Version:
# 0.1		18.07.2004	Tim Weippert
# 		Intial Creation of this file.
#
#
# Written by Tim Weippert <weiti@topf-sicret.org>
# Copyright: GPL
# 

use strict;
use vars qw($VERSION);

use Getopt::Long;
use POSIX qw(strftime);
use Net::LDAP;
use Net::DNS;

my $VERSION = 0.1;

#
# Get Opts from commandline
#
# Meanings:
#
# 	verbose		=> More Informations
# 	debug		=> debugging on/off
# 	ldap-server	=> IP/host of the LDAPServer to query (only one!)
# 	ldap-base	=> LDAP Base to join LDAP Tree
# 	query-ns	=> One or more Nameserver to look for Serial
# 			   If there are more than one, look on all serials
# 			   If they not match each other blame the DNS Admin!
# 	domain		=> Well the domain to query (NS and LDAP)
# 	domain-file	=> File to write the new zone if created! If ommited
# 			   File will be "db.$domain" in the actual directory"
# 	

# Initialize Commandline parameter Hash
my $verbose = 0;
my $debug = 0;
my $demo = 0;

my %params = ('verbose' => \$verbose, 'debug' => \$debug, 'demo' => \$demo);


# Get Options
GetOptions (\%params, 
	    'verbose', 
	    'debug', 
	    'demo',
	    'ldap-server=s', 
	    'ldap-base=s', 
	    'query-ns=s@', 
	    'domain=s',
	    'domain-file=s');

# Check for essential Commandline Parameters
if ( exists($params{'ldap-server'}) &&
     exists($params{'ldap-base'}) &&
     exists($params{'query-ns'}) &&
     exists($params{'domain'}) ) {
  # Needed Parameters set, if verbose or debug is set, print them out
  if ( $verbose || $debug ) { 
    print "Command Line Parameters:\n";
    foreach my $param ( sort keys %params ) {
      if ( ref($params{$param}) eq "ARRAY" ) {
        foreach my $element ( @{$params{$param}} ) {
          print "  Key $param	=> Value $element (Array)\n";
	}
      } elsif ( ref($params{$param}) eq "SCALAR" ) {
        print "  Key $param	=> Value ".${$params{$param}}."\n";
      } else {
        print "  Key $param	=> Value $params{$param}\n";
      }
    }
  }
} else {
  &usage();
}

# If no domain-file is given as param, create file db.$domain in the current
# directory
if ( ! exists($params{'domain-file'}) ) {
  $params{'domain-file'} = "db.".$params{'domain'};
}

print "dnsZone-Generator Version $VERSION started for Domain: $params{domain}\n";

# Now do the real hard work, first get SOA from all given Nameservers (--query-ns)
# We can be sure we get some query-ns because the params get checked above....

my $actual_ns_serial = &query_ns($params{'query-ns'}, $params{'domain'});
print "  Got Serial from NS: $actual_ns_serial\n";

my $actual_ldap_serial = &get_serial_from_ldap($params{'ldap-server'}, $params{'ldap-base'}, $params{'domain'});
print "  Got Serial from LDAP: $actual_ldap_serial\n";

if ( $actual_ns_serial && $actual_ldap_serial ) {
  if ( $actual_ldap_serial > $actual_ns_serial ) {
    # LDAP Serial is higher than NS Serial, we need to recreate
    # the Zone from LDAP
  } elsif ( $actual_ldap_serial < $actual_ns_serial ) {
    # Oh Oh, something really bad or stupid happens, don't touch the
    # Zone file, but cry..
    die "ERROR: LDAP Serial is smaller than NS Serial.\n This should NEVER happen, only if someone changed the NS Config by hand!\n";
  } else {
    # LDAP and NS Serial are equal, do nothing!
    print "  Serials are equal, nothing to do, cleaning up and exit (only in demo mode show zone file).\n";
    if ( ! $demo ) {
      print "dnsZone-Generator Version $VERSION ended for Domain: $params{domain} without errors\n";
      exit(0);
    }
  }
} else {
  if ( !$actual_ldap_serial ) {
    die "ERROR: Got only one Serial either from LDAP, can't generate Zonefile!\n";
  } else {
    warn "WARNING: Got only one Serial from NS, maybe new Zone!\n";
  }
}

#
# If we are here, we need to recreate the ZoneFile
#
my $Zone = &create_zone_hash_from_ldap($params{'ldap-server'}, $params{'ldap-base'}, $params{'domain'});

if ( $debug || $demo) {
  my $format = &format_zone_hash_to_string($Zone, 'STDOUT');
  eval $format;
  write;
}

if ( ! $demo ) {
  # Open Output File and write ZoneInformation only if i'am not in demo Mode
  print "  Write new Zonefile to $params{'domain-file'}\n";
  open(FILE, ">$params{'domain-file'}") or die "Can't open $params{'domain-file'}: $!\n";
    my $format = &format_zone_hash_to_string($Zone, 'FILE');
    eval $format;
    write FILE;
  close(FILE);
}
print "dnsZone-Generator Version $VERSION ended for Domain: $params{domain} without errors\n";
exit(0);

#       #
## Subs ##
#       #

sub usage() {
  # What we need as Parameters

  print "More Parameters needed ... write me :)\n";

  exit(5);
}

sub query_ns() {
  my $ns = shift;
  my $domain = shift;

  my %serials = ();
  my $last_serial = '';
  
  if ( $debug ) { print "Enter sub query_ns() with params: $ns, $domain\n"; }
  # $ns is always an array, because of GetOptions ... see above!
  foreach ( @{$ns} ) {
    # Iterate through all nameservers, maybe only one.
    my $serial = &get_serial_from_ns($_, $domain);
    if ( $serial ) {
      # Set $refernce_serial to the last gotten serial
      $serials{$serial}++;
      $last_serial = $serial;
    }
  }
  
  if ( keys %serials > 1 ) { 
    # Serials are not equal ... not good :)
    print "Serials on Nameserver not equal, stop working on this domain until fixed!\n";
    exit(1);
  } else {
    # Ok, when all serials are the same, or only one came back, we believe that
    # these serial is ok (normally we only query one Nameserver, the authoritative)
    return $last_serial;
  }
}

sub get_serial_from_ns() {
  my $ns = shift;
  my $domain = shift;
  my $resolver = undef;
  my $resolver_query = undef;
  my $serial = undef;
  my @ns_array = ( $ns ) ; # Generate Array of one NS, because Net::DNS::Resolver needs an Arrayref ...
  
  if ( $debug ) { print "Enter sub get_serial_from_ns() with params: $ns, $domain\n"; }
  
  # We got one Nameserver and ask them for the Serial of $domain
  $resolver = Net::DNS::Resolver->new(nameservers => \@ns_array, tcp_timeout => 5, udp_timeout => 5);
  $resolver_query = $resolver->query("$domain", "SOA");
  
  if ($resolver_query) {
    $serial = ($resolver_query->answer)[0]->serial;
    if ( $verbose || $debug ) { print "Serial on NS: $ns, $serial\n"; }
  } else {
    if ( $verbose || $debug ) { print "NS query failed on NS: $ns ", $resolver->errorstring, "\n"; }
  }

  return $serial;
}

sub get_serial_from_ldap() {
  my $ldap = shift;
  my $base = shift;
  my $domain = shift;
  my $filter = '(& (relativeDomainName=@) (zoneName='.$domain.'))';
  my $attrs = "sOARecord";
  my $serial = '';

  if ( $debug ) { print "Enter sub get_serial_from_ldap() with params: $ldap, $base, $domain\n"; }
  
  # Connect to the LDAP Server
  my $ldap_connect = Net::LDAP->new($ldap, onerror => undef);
  $ldap_connect->bind;

  my $ldap_mesg = $ldap_connect->search(
                base    => $base,
                filter  => $filter,  
		attrs   => [ $attrs ] );

  $ldap_mesg->code && print $ldap_mesg->error;

  if ( $ldap_mesg->count > 0 ) {
    # We got something from the LDAP, and only the first entry will be examined
    # Now we look for the serial number in this something ...
    my $entry = $ldap_mesg->entry(0);
    if ( $debug ) { print "LDAP sOARecord return: ".$entry->get_value("sOARecord")."\n"; }

    # Split sOARecord by spaces 
    my @ldap_soa_array = split(' ', $entry->get_value("sOARecord"));
    
    if ( $ldap_soa_array[2] ) {
      # The Serial should be on position 3 in the array.
      $serial = $ldap_soa_array[2];
    } 
  }

  $ldap_connect->unbind;

  return $serial;
}

sub create_zone_hash_from_ldap() {
  my $ldap = shift;
  my $base = shift;
  my $domain = shift;
  my $filter = 'zoneName='.$domain;
  my $attrs = '*';
  my %Zone = ();
  
  if ( $debug ) { print "Enter sub create_zone_hash_from_ldap() with params: $ldap, $base, $domain\n"; }
  
  # Connect to the LDAP Server
  my $ldap_connect = Net::LDAP->new($ldap, onerror => undef);
  $ldap_connect->bind;

  my $ldap_mesg = $ldap_connect->search(
                base    => $base,
                filter  => $filter,  
		attrs   => [ $attrs ] );

  $ldap_mesg->code && print $ldap_mesg->error;

  if ( $ldap_mesg->count > 0 ) {
    $ldap_mesg->sorted();
    # We got something from the LDAP, iterate through the results
    foreach my $entry ($ldap_mesg->all_entries) {
      my @attributes = $entry->attributes ( nooptions => 1 );

      if ( $entry->get_value('relativeDomainName') eq '@' ) {
        foreach my $attr ( @attributes ) {
          # Get Domain Head with SOA, NS, MX, etc ...
	  if ( $attr =~ /^sOARecord$/i) {
	    # SOA for the Zone, splitted in Zone Hash
	    my @soa = split(' ', $entry->get_value($attr));
            $Zone{'SOA'}{'MNAME'} = $soa[0];
            $Zone{'SOA'}{'RNAME'} = $soa[1];
            $Zone{'SOA'}{'SERIAL'} = $soa[2];
            $Zone{'SOA'}{'REFRESH'} = $soa[3];
            $Zone{'SOA'}{'RETRY'} = $soa[4];
            $Zone{'SOA'}{'EXPIRE'} = $soa[5];
            $Zone{'SOA'}{'MINIMUM'} = $soa[6];
	  }

	  if ( $attr =~ /^DNSTTL$/i ) {
	    # Default TTL which is set with $TTL in the first element of an Zone for compatibility
	    if ( my $ttl = $entry->get_value($attr) ) {
	      $Zone{'DefaultTTL'} = $ttl;
	    } else {
	      $Zone{'DefaultTTL'} = "86400";
	    }
          }
          if ( $attr !~ /(soarecord|objectclass|dnsttl|dnsclass|relativedomainname|zoneName)/i ) {
	    my $value = $entry->get_value ( $attr, asref => 1 );
            my $count = 1;
	    foreach my $val ( sort @{$value} ) {
	      $Zone{$attr}{$count} = $val;
	      $count++;
	    }
	  }
	}
      } else {
        # Normal RR
	# first we need the RelativeDomainName, because this is for identify the record ...
        my $rdns = $entry->get_value('relativeDomainName', asref => 1);

	foreach my $rdn ( @{$rdns} ) {
	  if ( $rdn !~ /CNAMEs/ ) { # CNAMEs is an fake rdn for cnames to this machine
            foreach my $attr ( @attributes ) {
              if ( $attr !~ /(soarecord|objectclass|dnsttl|dnsclass|zoneName|relativeDomainname)/i ) {
	        my $value = $entry->get_value ( $attr, asref => 1 );
                my $count = 1;
	        foreach my $val ( sort @{$value} ) {
	          $Zone{'RRs'}{$rdn}{$attr}{$count} = $val;
	          $count++;
	        }
	      }
	    }
	  }
	}
      }
    }
  }

  $ldap_connect->unbind;

  return \%Zone;
}

sub format_zone_hash_to_string() {
  my $Zone = shift; # Get Hash with Zoneinformation
  my $handle = shift; # Get Handle Name for Format
  
  my $DateTime = strftime "%d.%m.%Y %H:%M:%S", localtime;
  # Build on the fly format for 
  my $temp = "format $handle = \n"
  .";\n"
  ."; Zone generated by dnsZone-Generator " . '@' . '<' x length($VERSION) . "\n"
  .'$VERSION'."\n"
  ."; Last Creation at:  " . '@' . '<' x length($DateTime) . "\n"
  .'"'.$DateTime.'"'."\n"
  .";\n"
  .'$TTL @' . '<' x length($Zone->{'DefaultTTL'}) . "\n"
  .'$Zone->{DefaultTTL}' . "\n"
  ."\n"
  .'@	IN	SOA	@' . '<' x length($Zone->{'SOA'}{'MNAME'}) .' @' . '<' x length($Zone->{'SOA'}{'RNAME'}) . " (\n"
  .'"@", $Zone->{SOA}{MNAME}, $Zone->{SOA}{RNAME} ' . "\n"
  .'			@>>>>>>>>>>	; Serial' . "\n"
  .'$Zone->{SOA}{SERIAL}' . "\n"
  .'			@>>>>>>>>>>	; Refresh' . "\n"
  .'$Zone->{SOA}{REFRESH}' . "\n"
  .'			@>>>>>>>>>>	; Retry' . "\n"
  .'$Zone->{SOA}{RETRY}' . "\n"
  .'			@>>>>>>>>>>	; Expire' . "\n"
  .'$Zone->{SOA}{EXPIRE}' . "\n"
  .'			@>>>>>>>>>> )	; Minimum Cache TTL ' . "\n"
  .'$Zone->{SOA}{MINIMUM}' . "\n\n";
  
  if ( $Zone->{'nSRecord'} ) {
    # NS Records are set for domain use them
    foreach my $ns_idx ( keys %{$Zone->{'nSRecord'}} ) {
      $temp .= '	IN	NS	@' . '<' x length($Zone->{'nSRecord'}->{$ns_idx}) . "\n"
      	      .'$Zone->{nSRecord}->{' . $ns_idx . '}' . "\n";
    }
  }

  $temp .= "\n";
  
  if ( $Zone->{'mXRecord'} ) {
    # MX Records are set for domain use them
    foreach my $mx_idx ( keys %{$Zone->{'mXRecord'}} ) {
      $temp .= '	IN	MX	@' . '<' x length($Zone->{'mXRecord'}->{$mx_idx}) . "\n"
      	      .'$Zone->{mXRecord}->{' . $mx_idx . '}' . "\n";
    }
  }

  $temp .= "\n";
  
  if ( $Zone->{'aRecord'} ) {
    # MX Records are set for domain use them
    foreach my $a_idx ( keys %{$Zone->{'aRecord'}} ) {
      $temp .= '	IN	A	@' . '<' x length($Zone->{'aRecord'}->{$a_idx}) . "\n"
      	      .'$Zone->{aRecord}->{' . $a_idx . '}' . "\n";
    }
  }
  $temp .= "\n; RRs\n";
  
  # Well iterate through all RRs and do your best!
  foreach my $rr ( sort keys %{$Zone->{'RRs'}} ) {
    foreach my $attr ( sort keys %{$Zone->{'RRs'}->{$rr}} ) {
      foreach my $count ( keys %{ $Zone->{'RRs'}->{$rr}->{$attr} } ) {
        # puuuh i'm there, here should i can get the real value!
	for ( $attr ) {
	  if ( /^aRecord$/i ) { 
	    my $value = $Zone->{'RRs'}->{$rr}->{$attr}->{$count};
            $temp .= '@<<<<<<<<<<<<<<<<<<<<	IN	A	@' . '<' x length($value) . "\n"
      	      .'"'.$rr.'"'. ',"'. $value .'"' . "\n";
	  } elsif ( /^cNAMERecord$/i ) { 
	    my $value = $Zone->{'RRs'}->{$rr}->{$attr}->{$count};
            $temp .= '@<<<<<<<<<<<<<<<<<<<<	IN	CNAME	@' . '<' x length($value) . "\n"
      	      .'"'.$rr.'"'. ',"'. $value .'"' . "\n";
	  } elsif ( /^mXRecord$/i ) { 
	    my $value = $Zone->{'RRs'}->{$rr}->{$attr}->{$count};
            $temp .= '@<<<<<<<<<<<<<<<<<<<<	IN	MX	@' . '<' x length($value) . "\n"
      	      .'"'.$rr.'"'. ',"'. $value .'"' . "\n";
	  } elsif ( /^pTRRecord$/i ) { 
	    my $value = $Zone->{'RRs'}->{$rr}->{$attr}->{$count};
            $temp .= '@<<<<<<<<<<<<<<<<<<<<	IN	PTR	@' . '<' x length($value) . "\n"
      	      .'"'.$rr.'"'. ',"'. $value .'"' . "\n";
	  } else { 
	    print "unknown Record: $attr\n"; 
	  }
	}
      }
    }
  }
  
  $temp .= '.';
  
  return $temp;
}
