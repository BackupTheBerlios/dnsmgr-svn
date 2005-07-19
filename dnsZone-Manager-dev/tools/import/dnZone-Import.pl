#!/usr/bin/perl -w
#
# This Program parses an Bind 8/9 Zonefile and import this
# Zone into the given LDAP Server
#
# Version:
# 0.1		23.09.2004	Tim Weippert
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
# 	Zone-File	=> Local ZoneFile to parse
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
	    'zone-file=s');


