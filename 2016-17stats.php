<?php
    //Example of code that will go into a page on the website
    include('TableGrabber.php');
    $stats16 = TableGrabber::loadTable('stats16.csv');
    TableGrabber::renderTable($stats16);
    $stats16g = TableGrabber::loadTable('stats16g.csv');
    TableGrabber::renderTable($stats16g);
 