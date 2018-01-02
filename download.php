<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die;		// really this shouldn't come from a GET


// Required: albumID, format, track, code (redemption code so we can mark that download is happening)

$albumID	= $_POST['albumID'];
$formatID	= $_POST['formatID'];
$track		= $_POST['track'];
$code		= $_POST['code'];

require_once('classes.php');
$db = new DowncodeDB();

$filename = $db->fileNameForAlbumTrackExtension($albumID, $track, $formatID);

if (!$filename) die;		// This will show an error message on the form that calls this.

echo $filename;		// this gets returned to AJAX

// Now, let us initiate the actual download.

?>