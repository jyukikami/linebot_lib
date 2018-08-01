<?php
require_once __DIR__ . '/linebot.php';

$bot = new LineBotClass(false);

try {
	// 削除
	if (!empty($_GET['delete'])) {
		$bot->delete_liff($_GET['delete']);
	}
	// 全て削除
	if (!empty($_GET['all_delete'])) {
		// 全てのliff情報を取得
		$liff_list = $bot->get_liff_list();
		foreach ($liff_list['apps'] as $key => $value) {
			$bot->delete_liff($value['liffId']);
		}
	}
	// 追加
	if (!empty($_GET['liff_url'])) {
		$reuslt = $bot->add_liff($_GET['liff_url'],$_GET['liff_type']);
	}
	// 全てのliff情報を取得
	$liff_list = $bot->get_liff_list();

} catch (Exception $e) {
	
}

?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<script type="text/javascript">
		var error = "<?echo !empty($reuslt['message']) ? $reuslt['message'] : "";?>";
		if (error != "") alert("error:" + error);
	</script>
</head>
<body>
	<form method="get">
		<div style="margin-top: 50px;">
			<div style="text-align : center ;">
				url:<input type="text" name="liff_url" size="50" placeholder="追加するurl (httpsから始まるurl)">
				type:<select name="liff_type">
					<option value="full">full:100%</option>
					<option value="tall">tall:80%</option>
					<option value="compact">compact:50%</option>
				</select>
				<input type="submit" value="追加">
			</div>
			<table border="1" cellspacing="0" style="margin: 0 auto;">
				<tr>
					<th>liff_id</th>
					<th>type</th>
					<th>url</th>
					<th>削除</th>
				</tr>
				<? foreach ($liff_list['apps'] as $key => $value) { ?>
				<tr>
					<td><? echo $value['liffId']; ?></td>
					<td><? echo $value['view']['type']; ?></td>
					<td><a href="<? echo $value['view']['url']; ?>"><? echo $value['view']['url']; ?></a></td>
					<td><a href="?delete=<?echo $value['liffId'];?>">削除</a></td>
				</tr>
				<? } ?>
				<tr>
					<td colspan="4" align="center"><a href="?all_delete=true">全て削除</a></td>
				</tr>
			</table>
		</div>
	</form>
</body>
</html>