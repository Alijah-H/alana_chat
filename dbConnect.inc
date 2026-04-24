<?php
//             username is mine, change to  your username - remove jrhicsa
//             whichMachine  username    password  whichDatabaseToUse
//             ""   between "" insert your Database password found in .my.cnf
$mysqli = mysqli_connect("localhost", "sp5442", 'Taxidermist2$shrapnel', "sp5442");
if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
        }
?>
