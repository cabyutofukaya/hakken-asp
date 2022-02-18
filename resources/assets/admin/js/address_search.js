import KenAll from "ken-all";

let isAddressSearching = false;

$(function() {
    // 郵便番号を数字のみの入力で制御
    $("[name='zip_code']").on("keydown", function(e) {
        let k = e.keyCode;
        let str = String.fromCharCode(k);
        if (
            !(str.match(/[0-9]/) || (37 <= k && k <= 40) || k === 8 || k === 46)
        ) {
            return false;
        }
    });
    $("[name='zip_code']").on("keyup", function(e) {
        this.value = this.value.replace(/[^0-9]+/i, "");
    });
    $("[name='zip_code']").on("blur", function() {
        this.value = this.value.replace(/[^0-9]+/i, "");
    });

    // 郵便番号検索
    $(".addressSearch").on("click", async function(e) {
        e.preventDefault();

        if (!$("[name='zip_code']").val()) {
            alert("郵便番号を入力してください");
            return;
        }
        if (!/^[0-9]{7}$/.test($("[name='zip_code']").val())) {
            alert("郵便番号の入力形式が正しくありません（半角数字7桁）");
            return;
        }

        if (isAddressSearching) return;
        isAddressSearching = true;

        // 住所データ初期化
        $("[name='prefecture_code']").val("");
        $("[name='address1']").val("");
        $("[name='address2']").val("");

        const response = await KenAll($("[name='zip_code']").val()).finally(
            () => {
                // 検索中フラグOFF
                isAddressSearching = false;
            }
        );
        if (response && response.length > 0) {
            let address = response[0];

            // 住所データセット
            $("[name='prefecture_code'] option").each(function(i, elm) {
                if ($(elm).text() === address[0]) {
                    $("[name='prefecture_code']").val($(elm).val());
                    return false;
                }
            });

            $("[name='address1']").val(`${address[1]}${address[2]}`);
        }
    });
});
