#!/usr/bin/perl -w

###################################################################
#
#
#
#
###################################################################

use strict;
use DBI;
use OpenOffice::OODoc;

# Database parameters
my $database='Fermini';
my $hostname="localhost";
my $port='';
my $user="www-data";
my $password;
my $dsn = "DBI:mysql:database=$database;host=$hostname;port=$port";

# Input parameters
my $file=shift or die;
my @arg=@ARGV or die;


sub main {

	my $filename=shift;
	my @ids=@_;
	my $db = DBI->connect($dsn, $user, $password, {'RaiseError' => 1}) or die "Error connecting to DB $database!\n";

	# Create oocalc spreadsheet
# 	my $doc=new OpenOffice::OOCBuilder() or die "Can't construct OpenOffice::OOCBuilder object!\n";

	my $doc = ooDocument(
					file	=> "$filename.ods",
					create	=> 'spreadsheet',
					member => 'content'

		) or die "Can't construct OpenOffice::OODoc object!\n";

	query_database($db,$doc,@ids);
	$db->disconnect();

	# Generate the OOCalc file
	print STDERR "Creating file $filename... \n";
	$doc->save;

	return(1);
}


## DATABASE QUERY ##
sub query_database {

	my $db=shift;
	my $oodoc=shift;
	my @ids=@_; # A list of idFermentation
	my @data_rows=();
	my $number=0;

	foreach my $idFermentation (@ids) {

		my ($sth, $idSouche, $idCulture, $idPoste);
		my @data_rows;
		my $quote_idFermentation=$db->quote($idFermentation);

		# Prepare and execute query Fermentation
		my $query_Fermentation="SELECT * FROM Fermentation WHERE idFermentation=$quote_idFermentation";

		$sth = $db->prepare($query_Fermentation);
		$sth->execute();
		my $Fermentation=$sth->fetchrow_hashref();
		$sth->finish();

		# Get primary keys for the other tables
		$idSouche=$Fermentation->{'Souche_idSouche'};
		$idCulture=$Fermentation->{'ConditionCulture_idConditionCulture'};
		$idPoste=$Fermentation->{'Poste_idPoste'};

		printf STDERR ("Fermentation:%d; Souche:%d; Condition:%d; Poste:%d\n",$idFermentation,$idSouche,$idCulture,$idPoste);

		# Prepare and execute query Souche
		my $query_Souche="SELECT * FROM Souche WHERE idSouche=".$idSouche;

		$sth = $db->prepare($query_Souche);
		$sth->execute();
		my $Souche=$sth->fetchrow_hashref();
		$sth->finish();

		# Prepare and execute query Culture
		my $query_Culture="SELECT * FROM ConditionCulture WHERE idConditionCulture=".$idCulture;

		$sth = $db->prepare($query_Culture);
		$sth->execute();
		my $Culture=$sth->fetchrow_hashref();
		$sth->finish();

		# Prepare and execute query Poste
		my $query_Poste="SELECT * FROM Poste WHERE idPoste=".$idPoste;

		$sth = $db->prepare($query_Poste);
		$sth->execute();
		my $Poste=$sth->fetchrow_hashref();
		$sth->finish();

		# Prepare and execute query Acquisition
		my $query_Acquisition="SELECT * FROM Acquisition WHERE Fermentation_idFermentation=$quote_idFermentation ORDER BY temps";
		$sth = $db->prepare("$query_Acquisition");
		$sth->execute();

		# Store all rows in an array of hashref
		while (my $results=$sth->fetchrow_hashref()) {
			push @data_rows, $results;
		}

		$sth->finish();

		oocal_output($oodoc, $number++, \@data_rows, $Fermentation, $Souche, $Culture, $Poste);
	}

	my $bilan=$oodoc->getTable(0, 100, 20);
	$oodoc->renameTable($bilan, "Bilan");
}


## OOCalc spreadsheet generation ##
sub oocal_output {

	my ($doc, $number, $data_ref, $Fermentation, $Souche, $Culture, $Poste)=@_;
	my $idFermentation=$Fermentation->{'idFermentation'};
	my ($bilan,$feuille,$cell);

	my ($ligne,$colonne) = (0,0);

	$doc->appendTable("$idFermentation", 100, 20);
	$feuille = $doc->getTable("$idFermentation", 100, 20);
	
	# Create a title for this sheet

	$cell=$doc->getTableCell($feuille, $ligne++, $colonne);
	$doc->cellValue($cell, "Fermentation n° $idFermentation");
	$ligne++;

	# Store general informations

	foreach  my $hashref ($Fermentation, $Souche, $Culture, $Poste) {

		foreach my $key (keys %{$hashref}) {

			$cell=$doc->getTableCell($feuille, $ligne, $colonne);
			$doc->cellValueType($cell, 'string');
			$doc->cellValue($cell, $key);

			$cell=$doc->getTableCell($feuille, $ligne, $colonne+1);
			$doc->cellValueType($cell, typeof($hashref->{"$key"}));
			$doc->cellValue($cell, $hashref->{"$key"});

			$ligne++;

			print STDERR join(":",$idFermentation,$key,$hashref->{"$key"}),"\n";
		}

		$ligne++;
	}

	# Store column names

	$cell=$doc->getTableCell($feuille, $ligne++, $colonne);
	$doc->cellValue($cell, 'BEGIN_OF_DATA',);

	foreach my $col_name ('date', 'temps (h)', 'poids (g)', 'volume (L)', 'mCO2 (g/L)', 'vCO2 (g/L/h)', 'idPrelevement') {
		$cell=$doc->getTableCell($feuille, $ligne, $colonne++);
		$doc->cellValueType($cell, 'string');
		$doc->cellValue($cell, "$col_name");
	}
	$ligne++;
	$colonne=0;

	# Store collected data

	foreach my $row (@{$data_ref}) {

		foreach my $key ('dateAcquisition','temps','poids','volumeRestant','mCO2','vCO2','Prelevement_idPrelevement') {
			$cell=$doc->getTableCell($feuille, $ligne, $colonne++);
			$doc->cellValueType($cell, typeof($row->{"$key"}));
			$doc->cellValue($cell, $row->{"$key"} ? $row->{"$key"} : '');
		}

		$ligne++;
		$colonne=0;
	}

	$cell=$doc->getTableCell($feuille, $ligne, $colonne);
	$doc->cellValue($cell, 'END_OF_DATA',);
}


sub typeof {

	my $val=shift;

	return unless defined $val;
	return 'float' if ( $val =~ m/^\d+$/ );
	return 'float' if ( $val =~ m/^\d+\.\d+$/ );
	return 'string';
}

main($file,@arg);

exit(0);
