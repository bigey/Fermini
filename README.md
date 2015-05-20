[![Build Status](https://travis-ci.org/bigey/Fermini.svg?branch=master)](https://travis-ci.org/bigey/Fermini)

# Fermini

## What is Fermini?

Fermini is system for monitoring yeast fermentations.

In 2010, participating in a yeast phenotyping project, I took the initiative to design
a system to store data obtained from small volume fermentations.

* The Fermini system is dedicated to fermentation in small volumes (300 mL) that do not require a kinetic monitoring
* It facilitates the data management (weighing and sampling);
* The data are securerely stored in a database;
* Data can be exported in "one-click" in a file in ODS format (spreadsheet);

## Dependencies

Debian/Ubuntu

* apache2
* mysql-server
* mysql-client
* phpmyadmin

	$ su -
	# aptitude install apache2 mysql-server mysql-client phpmyadmin
	

Perl modules

	# cpan -i CPAN::Bundle YAML Term::ReadLine::Perl Device::SerialPort DBI OpenOffice::OODoc

## Install

### Download project

Download from: https://github.com/bigey/Fermini

	# unzip Fermini-master.zip
	# mkdir /var/www/Fermini
	# mv -r Fermini-master/* /var/www/Fermini
	
### Create Fermini database

edit install_fermini.sh:

	# cd /var/www/Fermini/Install
	# nano install_fermini.sh

set root MySQL password:

	DB_ROOT_USER='root'
	DB_ROOT_PASS=<root-mysql-user-password>

create database:

	# /var/www/Fermini/Install/install_fermini.sh
	# adduser www-data dialout

### Modify the balance serial communication script

In the Perl script `/Scripts/balance_ohaus.pl`

Change the serial port accordingly:

	my $device = "/dev/ttyS0";

In function `initDevice` bellow, change `baudrate, parity, databits, stopbits, handshake` according to your balance model.

~~~
sub initDevice {

	my $port = shift;
	my $PortObj = shift;

	$PortObj = Device::SerialPort->new($port) or die "Can't start $port\n";

	# Serial device parameters
	$PortObj->baudrate(2400);      # 
	$PortObj->parity("none");      #
	$PortObj->databits(7);         #  Change serial parameters here!
	$PortObj->stopbits(1);         #
	$PortObj->handshake("xoff");   #
	$PortObj->write_settings;

	$PortObj->save("Ohaus.conf");

	return $PortObj;
}
~~~

In function `parseWeight` bellow, change the parsing regex according to the output format of you balance. 

~~~
sub parseWeight {

	my $string = shift;
	my $weight;

	if ($string =~ m/(\d+\.\d\d) g \r\n/) { # <- change the regex here
		$weight = $1;
		return($weight);
	} else {
		warn "Weight parsing error!";
		return(undef);
	}
}
~~~

### Regular backup of database (optional)

	# mkdir /home/<user>/bin
	# cp -a /var/www/Fermini/Install/mysqldump.sh /home/<user>/bin

Create an entry in crontab:

	# crontab -e
	
	# m h dom mon dow   command
	0 */2 * * * /home/<user>/bin/run-mysqldump.sh
	Ctl+D
