<div class="wrap">
	<h2>ベーシック認証設定</h2>

	<?php if ($GLOBALS["caret2sa"]['action'] === "update"): ?>
	<?php if ($GLOBALS["caret2sa"]['error']): ?><div class="error" style="width: 50%;height: 40px;line-height: 40px;">入力に誤りがあります。<?php else: ?><div class="updated" style="width: 50%;height: 40px;line-height: 40px;">更新が完了しました。<?php endif; ?></div>
	<?php endif; ?>

	<ul style="list-style-type:decimal;color:red;font-size:12px;line-height:0.7em;margin-left:20px;">
		<li>初めてエントリを追加した場合は、設定直後に認証が必要となります。</li>
		<li>認証済みのエントリを変更した場合は、設定直後に再認証が必要となります。</li>
		<li>大文字小文字は区別しますので、ご注意ください。</li>
		<li>IDまたはパスワードを忘れてしまいログインできなくなった場合は、FTPでプラグインを削除するか、「<?php echo CARET2SA_TABLE; ?>」テーブルのエントリを全て削除してください。</li>
		<li>エントリが0件の場合(何も設定されていない場合)、ベーシック認証は行われません。</li>
	</ul>

	<form method="post" action="options-general.php?page=CaretTwoStageAuthentication">
	<input type="hidden" name="action" value="update" />

	<table class="widefat" style="width: 50%;">
	<thead>
	<tr>
		<th style="text-align:center;border-right: 1px solid #ccc;">認証ID</th>
		<th style="text-align:center;">認証パスワード</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$max = count($GLOBALS["caret2sa"]['user']);

		for ($i = 0; $i < $max; $i++) {
			if (!$GLOBALS["caret2sa"]['user'][$i] && !$GLOBALS["caret2sa"]['passwd'][$i]) continue;
	?>

	<tr>
		<td style="border-right: 1px solid #ccc;">
			<input type="text" name="user[]" value="<?php echo esc_attr($GLOBALS["caret2sa"]['user'][$i]); ?>" size="40" maxlength="32" class="search-input"<?php if ($GLOBALS["caret2sa"]['error']['user'][$i]): ?> style="background-color:#ffebe8;"<?php endif; ?> />
			<?php if ($GLOBALS["caret2sa"]['error']['user'][$i]): ?><div style="color:red;"><?php echo esc_attr($GLOBALS["caret2sa"]['error']['user'][$i]); ?></div><?php endif; ?>
		</td>
		<td>
			<input type="password" name="passwd[]" value="<?php echo esc_attr($GLOBALS["caret2sa"]['passwd'][$i]); ?>" size="40" maxlength="32" class="search-input"<?php if ($GLOBALS["caret2sa"]['error']['passwd'][$i]): ?> style="background-color:#ffebe8;"<?php endif; ?> />
			<?php if ($GLOBALS["caret2sa"]['error']['passwd'][$i]): ?><div style="color:red;"><?php echo esc_attr($GLOBALS["caret2sa"]['error']['passwd'][$i]); ?></div><?php endif; ?>
		</td>
	</tr>
	<?php
		}
	?>

	<tr>
		<td style="border-right: 1px solid #ccc;border-bottom:0;">
			<input type="text" name="user[]" value="<?php echo esc_attr($GLOBALS["caret2sa"]['user'][$i]); ?>" size="40" maxlength="32" class="search-input"<?php if ($GLOBALS["caret2sa"]['error']['user'][$i]): ?> style="background-color:#ffebe8;"<?php endif; ?> />
			<?php if ($GLOBALS["caret2sa"]['error']['user'][$i]): ?><div style="color:red;"><?php echo esc_attr($GLOBALS["caret2sa"]['error']['user'][$i]); ?></div><?php endif; ?>
		</td>
		<td style="border-bottom:0;">
			<input type="password" name="passwd[]" value="<?php echo esc_attr($GLOBALS["caret2sa"]['passwd'][$i]); ?>" size="40" maxlength="32" class="search-input"<?php if ($GLOBALS["caret2sa"]['error']['passwd'][$i]): ?> style="background-color:#ffebe8;"<?php endif; ?> />
			<?php if ($GLOBALS["caret2sa"]['error']['passwd'][$i]): ?><div style="color:red;"><?php echo esc_attr($GLOBALS["caret2sa"]['error']['passwd'][$i]); ?></div><?php endif; ?>
		</td>
	</tr>
	</tbody>
	</table>

	<p><input type="submit" class="button" value="　　更　新　　" /></p>

	</form>
</div>