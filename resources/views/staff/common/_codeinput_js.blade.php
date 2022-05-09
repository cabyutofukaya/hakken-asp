<script>
    (function(){
        // コード(商品コード、仕入先コード)専用入力フィールド。半角英数とハイフン、スラッシュのみ入力可能
        if (!Element.prototype.matches) Element.prototype.matches = Element.prototype.msMatchesSelector;
        const filter = function(e){
            let v = e.target.value.replace(/[０-９ａ-ｚＡ-Ｚ]/g, function(x){ return String.fromCharCode(x.charCodeAt(0) - 0xFEE0) }).replace(/[^0-9a-zA-Z\-\/]/g, '');
            e.target.value = v;
        };

        let isComposing = false; // IE11対応が不要の場合は InputEvent.isComposing が使用可
        document.addEventListener('input', function(e){
            if (!isComposing && e.target.matches("input.codeInput")) filter(e)
        });
        document.addEventListener('compositionstart', function(e){
            if (e.target.matches("input.codeInput")) {
                isComposing = true;
            }
        });
        document.addEventListener('compositionend', function(e){
            if (e.target.matches("input.codeInput")) {
                isComposing = false;
                setTimeout(function(){ filter(e) }, 0);
            }
        });
    })();
</script>