<?php

/**
 * ２つの文字列をdiffコマンドに渡して結果を配列で返します。
 * @param string $s1
 * @param string $s2
 * @param string $diffOptions e.g.)"-U2 --strip-trailing-cr"
 * @return string[] diffコマンドの出力。差分がないときは空配列となります。
 * @throws RuntimeException 予期せぬ終了コードが発生したとき
 */
function string_diff($s1, $s2, $diffOptions) {
  if ($s1 === $s2) {
    return array();
  }

  $output = array();
  $exitCode = null;

  // 外部コマンド実行は状況によって失敗することもあるので3回ほどリトライする。
  for ($i = 0; $i < 3; ++$i) {
    $file1 = tempnam(sys_get_temp_dir(), 'tmp1');
    file_put_contents($file1, $s1);
    $file2 = tempnam(sys_get_temp_dir(), 'tmp2');
    file_put_contents($file2, $s2);

    $output = array();
    $exitCode = null;
    exec("diff $diffOptions '$file1' '$file2'", $output, $exitCode);

    unlink($file1);
    unlink($file2);

    // 1 = 差分あり
    if ($exitCode !== 0 && $exitCode !== 1) {
      continue;
    }
  }

  if ($exitCode !== 0 && $exitCode !== 1) {
    throw new RuntimeException("Command execution failed. [code:$exitCode]", $exitCode);
  }

  return $output;
}
