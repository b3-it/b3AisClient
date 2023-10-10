<?php
error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '127.0.0.1';
$port = 10000;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Send instructions. */
    $msg =
	"!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\n" .
	"!AIVDM,1,1,,B,13eK4H01B7PPVj4OD@seDrWb84hD,0*77\n" .
	"!AIVDM,1,1,,A,E>j9driW9WhH@860b37a6P00000@ATmQ?Tnh800000sh20,4*56\n" .
	"!AIVDM,1,1,,A,339NQJP01HPe90`N`s@:HpKb020@,0*4B\n" .
	"!AIVDM,1,1,,A,13A=Ip001t0QNBTO090:n`eh0D25,0*21\n" .
	"!AIVDM,1,1,,B,139dl4?00B0RvwpNiM<=2rKh0@Pl,0*08\n" .
	"!AIVDM,1,1,,B,13MA0?@Oiw0Ml?RNdhRPuPSf08Pm,0*2C\n" .
	"!AIVDM,1,1,,B,33b51:700KPLc8FN`Rt:98Ah00ri,0*05\n" .
	"!AIVDM,1,1,,B,139NVn?P00PcVVDNcqi8BOwf28Pn,0*55\n" .
	"!AIVDM,1,1,,A,33@Tt2001<PW@J4N`R1b087d00vP,0*12\n" ;

		
    socket_write($msgsock, $msg, strlen($msg));
    socket_close($msgsock);
} while (true);

socket_close($sock);
?>