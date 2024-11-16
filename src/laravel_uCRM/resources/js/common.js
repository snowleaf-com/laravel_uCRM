const nl2br = (str) => {
    // str が null または undefined または 空文字列の場合、空文字列を返す
    if (!str) {
        return '';
    }

    // 改行コードを <br /> に置き換える
    str = str.replace(/\r\n/g, "<br />");
    str = str.replace(/(\n|\r)/g, "<br />");

    return str;
}

// 関数を別ファイルで使い回すための記述
export { nl2br }