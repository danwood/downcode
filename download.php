<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die;		// really this shouldn't come from a GET

function sendStatusCode($statusCode)
{
    header(' ', true, $statusCode);
}

// Required: albumID, format, track, code (redemption code so we can mark that download is happening)

$albumID	= $_POST['a'];
$formatID	= $_POST['f'];
$trackID	= $_POST['t'];
$code		= $_POST['c'];

if (!$albumID || !$formatID || !$code) {
	error_log("403 error: Missing Parameters");
	sendStatusCode(403);
	echo "Parameters not specified";
	die;
}

require_once('classes.php');
$db = new DowncodeDB();

$filename = $db->fileNameForAlbumTrackExtension($albumID, $trackID, $formatID);

if (!$filename) {
	error_log("403 error: Not finding albumID: $albumID trackID: $trackID");
	sendStatusCode(403);
	echo "File not specified";
	die;
}

// Now, let us initiate the actual download.

$filename = str_replace('/','_', $filename);	// protect against baddies

$path = DOWNCODE_FILESDIR . '/' . $filename;
if (file_exists($path))
{
	// Update the database to indicate that download has happened
	$db->updateRedemption($code);
	$db->updateDownloadCount($albumID, $trackID);
	$db->updateFormatCount($formatID);

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
	error_log("404 error: Not finding $path");
	sendStatusCode(404);	// file doesn't exist
	echo "File not found";
}
?>