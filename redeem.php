<?php
require_once('classes.php');
$iOSDevice = false;       // or set to a non-false text value
$userAgent = $_SERVER['HTTP_USER_AGENT'];
if (preg_match("/(\\(iPod|\\(iPhone|\\(iPad)/", $userAgent, $matches)) {
    $iOSDevice = substr($matches[1], 1);
}
$code = isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '';
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';

$db = new DowncodeDB();
// $formats = $db->formats();
// foreach ($formats as $entry) {
// 	echo $entry['extension'] . ' ... ' . $entry['description'] . PHP_EOL;
// }

$album = $db->findAndRedeemAlbumFromCode($code, $iOSDevice);

if ($album) {
?>
<h1><?php echo htmlspecialchars($album['title']); ?></h1>
<p><?php echo htmlspecialchars($album['description']); ?></p>
<img src="albums/<?php echo htmlspecialchars($album['imageName']); ?>" alt="<?php echo htmlspecialchars($album['title']); ?>" />
<table border="1">
<?php
	$tracks = $db->tracksOfAlbumID($album['ID']);
	foreach ($tracks as $track) {
?>
	<tr>
		<td><?php echo htmlspecialchars($track['trackNumber']); ?></td>
		<td><?php echo htmlspecialchars($track['title']); ?></td>
		<td><button></td>	<!-- use fileBase -->
	</tr>
<?php
}
?>
</table>
<?php
if (!$iOSDevice) {
	echo '<h2>Format:</h2>' . PHP_EOL;
	echo '<select name="format">' . PHP_EOL;
	$formats = $db->formats();
	foreach ($formats as $format) {
		echo '<option value="' . $format['extension'] . '"';
		if (isset($album['formatID']) && $album['formatID'] == $format['ID']) {
			echo ' selected data-previous-format="' . $album['formatID'] . '"';
		}
		else if (!isset($album['formatID'])
			&& isset($format['platform_preg'])
			&& preg_match($format['platform_preg'], $userAgent, $matches)) {
			echo ' selected data-platform="' . $matches[0] . '"';
		}
		echo '>' . htmlspecialchars($format['description']) . '</option>' . PHP_EOL;
	}
	echo '</select>' . PHP_EOL;
}
?>
<?php
}
else
{
?>
<p>
Sorry, but the code you used is not valid, or has already been redeemed. Please double-check the code and try again. If you are sure this is an error, please contact webmaster@lorenzowoodmusic.com with the code that you used.
</p>
<?php
}
?>