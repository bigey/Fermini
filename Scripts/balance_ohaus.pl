#!/usr/bin/perl -w


use Device::SerialPort;
use strict;

my $device = "/dev/ttyS0";
my $cmd = shift or die;

sub initDevice {

	my $port = shift;
	my $PortObj = shift;

	$PortObj = Device::SerialPort->new($port) or die "Can't start $port\n";

	# Serial device parameters
	$PortObj->baudrate(2400);
	$PortObj->parity("none");
	$PortObj->databits(7);
	$PortObj->stopbits(1);
	$PortObj->handshake("xoff");
	$PortObj->write_settings;

	$PortObj->save("Ohaus.conf");

	return $PortObj;
}


sub sendCmd {

	my $PortObj = shift;
	my $cmd = shift;

	unless ( $PortObj->write("$cmd\r") ) {
		warn "Sending command timeout!\n";
		return(undef);
	}

	return(1);
}


sub waitResponse {

	my $PortObj = shift;
	my $string = "";
	my $timeout = 10;
	my $weight;

	while ($string eq '') {
		$string = $PortObj->input;
		$timeout--;
		sleep 1;
		last if $timeout==0;
	}

	if ($string ne '') {
		return($string);

	} else {
		warn "Timeout response!\n";
		return(undef);
	}
}


sub parseWeight {

	my $string = shift;
	my $weight;

	if ($string =~ m/(\d+\.\d\d) g \r\n/) {
		$weight = $1;
		return($weight);
	} else {
		warn "Weight parsing error!";
		return(undef);
	}
}


sub closeDevice {

	my $PortObj = shift;
	$PortObj->close or warn "Close device failed: $!\n";
	undef $PortObj;  # closes port AND frees memory in perl
}


sub command {

	my $serialPort = shift;
	my $command = shift;
	my $response;

	sendCmd($serialPort, $command) or return(undef);
	$response = waitResponse($serialPort) or return(undef);

	$response = parseWeight($response) if ($command eq 'SP');

	return($response);
}


sub main {

	my $command = shift;
	my $serialPort;
	my $value;

	$serialPort = initDevice($device, $serialPort);
	$value = command($serialPort, $command);
	closeDevice($serialPort);

	if ($value) {
		chomp $value;
		print "$value\n";
		return 1;
	} else {
		return 0;
	}
}

main($cmd) ? exit(0) : exit(1);