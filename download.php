<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die;		// really this shouldn't come from a GET

function sendStatusCode($statusCode)
{
    header(' ', true, $statusCode);
}


// Required: albumID, format, track, code (redemption code so we can mark that download is happening)

$albumID	= $_POST['albumID'];
$formatID	= $_POST['formatID'];
$track		= $_POST['track'];
$code		= $_POST['code'];

require_once('classes.php');
$db = new DowncodeDB();

$filename = $db->fileNameForAlbumTrackExtension($albumID, $track, $formatID);

if (!$filename) { sendStatusCode(403); echo "File not specified"; die; }		// This will show an error message on the form that calls this.

// Now, let us initiate the actual download.

$filename = str_replace('/','_', $filename);	// protect against baddies

$path = DOWNCODE_FILESDIR . '/' . $filename;
if (file_exists($path))
{
	$fp = fopen($path, 'rb');

	// send the right headers
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . filesize($path));
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header("Content-Description: File Transfer");
	header("Cache-Control: public");
	header("Content-Transfer-Encoding: binary");

	// dump the picture and stop the script
	fpassthru($fp);
	exit;
}
else
{
	sendStatusCode(404);	// file doesn't exist
	echo "File not found";
}
?>