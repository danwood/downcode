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

$album = $db->findAndRedeemAlbumFromCode($code, $iOSDevice);

if ($album) {
?>
<h1><?php echo htmlspecialchars($album['title']); ?></h1>
<p><?php echo htmlspecialchars($album['description']); ?></p>
<img src="albums/<?php echo htmlspecialchars($album['imageName']); ?>" alt="<?php echo htmlspecialchars($album['title']); ?>" />
<form id="downloader">
	<input type="hidden" name="albumID" value="<?php echo $album['ID']; ?>" />
	<input type="hidden" name="code" value="<?php echo $code; ?>" />
	<table border="1">
<?php
	if (!$iOSDevice) {
?>
		<tr><td colspan="3"><button type="button" class="download" name="track" value="0">Download all</button></td></tr>
<?php
	}
	$tracks = $db->tracksOfAlbumID($album['ID']);
	foreach ($tracks as $track) {
?>
		<tr>
			<td><?php echo $track['trackNumber']; ?></td>
			<td><?php echo htmlspecialchars($track['title']); ?></td>
<?php
	if (!$iOSDevice) {
?>
			<td><button type="button" class="download" name="track" value="<?php echo $track['trackNumber']; ?>">Download</button></td>	<!-- use fileBase -->
<?php
	}
?>
		</tr>
<?php
}
?>
	</table>
<?php
if (!$iOSDevice) {
	echo '<h2>Format:</h2>' . PHP_EOL;
	echo '<select name="formatID">' . PHP_EOL;
	$formats = $db->formats();
	foreach ($formats as $format) {
		echo '<option value="' . $format['ID'] . '"';
		echo ' data-extension="' . $format['extension'] . '"';	// not really used, but maybe a script could populate something?
		if (isset($album['formatID']) && $album['formatID'] == $format['ID']) {
			echo ' data-previous-format="' . $album['formatID'] . '" selected';
		}
		else if (!isset($album['formatID'])
			&& isset($format['platform_preg'])
			&& preg_match($format['platform_preg'], $userAgent, $matches)) {
			echo ' data-platform="' . $matches[0] . '" selected';
		}
		echo '>' . htmlspecialchars($format['description']) . '</option>' . PHP_EOL;
	}
	echo '</select>' . PHP_EOL;
}
?>
</form>


<!-- jquery already loaded ??? -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<script>
$('button.download').click(function (evt) {
    evt.preventDefault();

    var button = $(evt.target);
    var serialized = button.parents('form').serialize()
        + '&'
        + encodeURI(button.attr('name'))
        + '='
        + encodeURI(button.attr('value'));

    $.ajax({
      type: 'POST',
      url: '/download.php',
      data: serialized,

      success: function(data, textStatus, jqXHR ) {
      	data = data.trim();
      	if ('' === data) {
      		alert('Unable to download. Probably an error with the website!');
      	}
      	else {
      		alert(data);
      	}
      },
      error: function(jqXHR, textStatus, errorThrown ) {
            alert(errorThrown + ' ' + textStatus);
      },
      complete: function(jqXHR, textStatus ) {
      }
    });

});

</script>


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

$db->close();
?>




