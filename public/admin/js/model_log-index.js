(window.webpackJsonp=window.webpackJsonp||[]).push([[8],{332:function(t,e,o){t.exports=o(333)},333:function(t,e){$((function(){$(".show-detail").on("click",(function(t){var e=$(this).data("json");$("#modelTxt").text("".concat(e.model," #").concat(e.model_id)),$("#roleTxt").text("".concat(e.guard," #").concat(e.user_id)),$("#operationTxt").text(e.operation_type),$("#messageTxt").html(JSON.stringify(JSON.parse(e.message),null,"\t")),$("#createdAtTxt").text(e.created_at)}))}))}},[[332,0]]]);