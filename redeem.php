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
<div id="redeem_console">
<h1><?php echo htmlspecialchars($album['title']); ?></h1>
<p><?php echo htmlspecialchars($album['description']); ?></p>
<img src="albums/<?php echo htmlspecialchars($album['imageName']); ?>" alt="<?php echo htmlspecialchars($album['title']); ?>" />
<form id="downloader">
	<input type="hidden" id="albumID" name="albumID" value="<?php echo $album['ID']; ?>" />
	<input type="hidden" id="code" name="code" value="<?php echo $code; ?>" />
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
	echo '<select id="formatID" name="formatID">' . PHP_EOL;
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
</div> <!-- redeem_console -->


<!-- jquery already loaded ??? -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<script>
$('button.download').click(function (evt) {
    evt.preventDefault();

    var button = $(evt.target);
    // Build a form
    // Based on this: https://gist.github.com/DavidMah/3533415
    var form = $('<form>').attr('action', 'download.php').attr('method', 'post');
    form.append($("<input>").attr('type', 'hidden').attr('name', button.attr('name')).attr('value', button.attr('value')));
    form.append($("<input>").attr('type', 'hidden').attr('name', 'albumID')          .attr('value', $('#albumID').val()));
    form.append($("<input>").attr('type', 'hidden').attr('name', 'code')             .attr('value', $('#code').val()));
    form.append($("<input>").attr('type', 'hidden').attr('name', 'formatID')         .attr('value', $('#formatID').val()));
    form.appendTo('body').submit().remove();

    // Dim the download console for just a second so we know that something is happening when we click download. Good or dumb idea?
    $( "#redeem_console" ).animate({
        opacity: 0.25,
      }, 100, function() {
        setTimeout(function(){
            $('#redeem_console').css({"opacity":"1.0"});
        }, 900);
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




