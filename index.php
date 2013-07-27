<?php

// Pepatung Framework

	// include modules
	include "modules.php";

	$pepatung = new pepatung;

	$pepatung->setTemplate("main");


	$pepatung->p('This is an example index built using Pepatung Framework.');

	// display output

	echo $pepatung->output();

?>