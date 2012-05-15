<!DOCTYPE html><?
require_once "RapidForm.php";

$fields = array(
	'username' => new RFTextWidget('Username'),
	'realname' => new RFSingleRow('Real Name', new RFTextWidget(), 'realname'),
	'nickname' => new RFRow('Nick Name', array(
		'nickname' => new RFTextWidget()
	)),
	'altname' => new RFRow('Alt Name', array(
		'altname' => new RFTextWidget()
	), '%s Alt name is alternative name for duplicated nickname'),
	new RFSpaceRow(),
	'save' => new RFSubmitWidget(),
);
$data = array_merge($_POST, array(
	'username' => 'default',
	'realname' => 'John Doe',
	'save' => 'Save Profile',
));

$form = new RFForm($fields, $data);
?>
<html>
<style>
dd, dt { float: left }
dd { clear: left }
</style>
<h1>Sign up</h1>
<form action="" method="POST">
<?=$form->as_dl()?>
</form>
</body>
</html>