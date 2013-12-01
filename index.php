<?php

/*
 *
 *  Pepatung PHP Framework
 *
 *  Proudly coded by @akifrabbani
 *  I love PHP <3
 *
 */

// include the main file of Pepatung Framework
include "modules.php";

// initialize the class!
$pepatung = new pepatung;

// select default site template
$pepatung->setTemplate('main');

// set page title
$pepatung->pageTitle('Welcome to Pepatung!');

// display message
$pepatung->p('<h4>Proudly powered by Pepatung.</h4>');

// output the output
echo $pepatung->output();

?>