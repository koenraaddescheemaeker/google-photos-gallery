<?php
/** * FORCEKES - logout.php (De Nette Exit) */
require_once 'config.php';

// De uil wist de sporen
session_unset();
session_destroy();

// En we vliegen terug naar de startpagina
header("Location: index.php");
exit;