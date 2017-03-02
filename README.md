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

For Debian/Ubuntu:

```bash
$ su -
aptitude install apache2 mysql-server mysql-client phpmyadmin
```	

Install Perl modules from CPAN:

```bash
cpan -i CPAN::Bundle YAML Term::ReadLine::Perl Device::SerialPort DBI OpenOffice::OODoc
```

## Install

### Download project

Clone from GitHub:

```bash
cd /var/www
git clone https://github.com/bigey/Fermini.git
cd Fermini
```

Or download sources from [GitHub](https://github.com/bigey/Fermini/archive/master.zip):

```bash
unzip Fermini-master.zip
mkdir /var/www/Fermini
mv -r Fermini-master/* /var/www/Fermini
cd /var/www/Fermini
```

### Create Fermini database

Edit the scrip `create_fermentation_db.sh`:

```bash
cd /var/www/Fermini/Install
nano create_fermentation_db.sh
```

Set the password for the `root` MySQL account:

```bash
DB_ROOT_USER='root'
DB_ROOT_PASS=<root-mysql-user-password>
```

Edit the scrip `fermini_db_creation.sql`:

```bash
cd /var/www/Install
nano fermini_db_creation.sql
```

Set the password for the `www-data` (apache2) user accessing the `Fermini` database in the line bellow:

```sql
CREATE USER 'www-data'@'localhost' IDENTIFIED BY 'password';
```

Then create the database:

```bash
bash /var/www/Fermini/Install/create_fermentation_db.sh
```

Edit the scrip `constantes.php`:

```
cd /var/www/Fermini
nano /var/www/Fermini/constantes.php
```

Set password in the line bellow:

```php
define("USER","www-data");
define("PASS", "password");
```

Edit the scrip `export_2_oocalc.pl`:

```bash
cd /var/www/Scripts
nano export_2_oocalc.pl
```

Set the password in the line bellow:

```perl
my $user="www-data";
my $password="password";
```


### Setup the configuration for your balance

The balance used here is OHAUS model Adventurer Pro AV812C (Max: 810 g, d: 0.01 g),
which is an entry level balance. You can use any other manufacturers or models 
supporting a serial communication. 

In order to access the serial port you need to add the user `www-data` to the `dialout` group:

```bash
adduser www-data dialout
```

In the Perl script `/Scripts/balance_ohaus.pl`

Change the serial port accordingly (eg. `/dev/ttS0`, `/dev/ttS1`):

```perl
my $device = "/dev/ttyS0";
```

In function `initDevice` bellow, change `baudrate, parity, databits, stopbits, handshake` according to your manufacturer specification:

```perl
sub initDevice {

	my $port = shift;
	my $PortObj = shift;

	$PortObj = Device::SerialPort->new($port) or die "Can't start $port\n";

	# Serial device parameters
	$PortObj->baudrate(2400);      # \
	$PortObj->parity("none");      #  |
	$PortObj->databits(7);         #  |Change parameters here!
	$PortObj->stopbits(1);         #  |
	$PortObj->handshake("xoff");   # /
	$PortObj->write_settings;

	$PortObj->save("Ohaus.conf");

	return $PortObj;
}
```

In function `parseWeight` bellow, change the parsing regex according to the output format of you balance:

```perl
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
```

### Set a regular backup of database (optional)

```bash
mkdir /home/<user>/bin
cp -a /var/www/Fermini/Install/mysqldump.sh /home/<user>/bin
```

Create an entry in crontab:

```
crontab -e

# m h dom mon dow   command
0 */2 * * * /home/<user>/bin/run-mysqldump.sh
Ctl+D
```
