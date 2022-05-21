<?php
namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\ViewComposers\Admin as VCAdmin;
use App\Http\ViewComposers\Staff as VCStaff;

class ComposerServiceProvider extends ServiceProvider
{
    public function boot(Request $request)
    {
        View::composer('admin.agency.index', VCAdmin\Agency\IndexFormComposer::class); // 会社情報一覧
        View::composer('admin.agency.create', VCAdmin\Agency\CreateFormComposer::class); // 会社情報作成
        View::composer('admin.agency.edit', VCAdmin\Agency\EditFormComposer::class); // 会社情報編集

        View::composer('admin.web.web_user.edit', VCAdmin\WebUser\EditFormComposer::class); // webユーザー編集画面


        View::composer('staff.*', function ($view) use ($request) {
            $view->with('agencyAccount', $request->agencyAccount);//routeのagencyAccountプリフェックスをセット
        });

        // ニュース
        View::composer('staff.common.news', VCStaff\Common\NewsComposer::class);
        View::composer('staff.common.news_alert', VCStaff\Common\NewsAlertComposer::class);

        // 予約・見積
        View::composer('staff.reserve.index', VCStaff\ReserveEstimate\ReserveIndexFormComposer::class); // 予約一覧
        View::composer('staff.estimate.index', VCStaff\ReserveEstimate\EstimateIndexFormComposer::class); // 見積一覧
        View::composer('staff.departed.index', VCStaff\ReserveEstimate\DepartedIndexFormComposer::class); // 催行一覧

        View::composer('staff.consultation.index', VCStaff\Consultation\IndexFormComposer::class); // 相談一覧
        View::composer('staff.consultation.message_index', VCStaff\Consultation\MessageFormComposer::class); // メッセージ履歴一覧

        // 予約・見積作成（createは予約・見積共通）
        View::composers([
            VCStaff\ReserveEstimate\CreateFormComposer::class => [
                'staff.reserve.create', 
                'staff.estimate.create',
            ],
        ]);
        // 予約・見積編集（editは予約・見積共通）
        View::composers([
            VCStaff\ReserveEstimate\EditFormComposer::class => [
                'staff.reserve.edit', 
                'staff.estimate.edit',
            ],
        ]);
        // 予約・見積詳細（showは予約・見積・催行済共通）
        View::composers([
            VCStaff\ReserveEstimate\ShowFormComposer::class => [
                'staff.reserve.show', 
                'staff.estimate.show',
                'staff.departed.show',
            ],
        ]);

        // 予約旅程
        // 作成（createは予約・見積もり共通）
        View::composers([
            VCStaff\ReserveEstimateItinerary\CreateFormComposer::class => [
                'staff.reserve_itinerary.create', 
                // 'staff.estimate_itinerary.create',
            ],
        ]);
        View::composer('staff.reserve_itinerary.edit', VCStaff\ReserveEstimateItinerary\EditFormComposer::class); // 編集ページ
        View::composer('staff.reserve_itinerary.pdf.itinerary', VCStaff\ReserveEstimateItinerary\Pdf\ItineraryComposer::class); // 行程PDF
        View::composer('staff.reserve_itinerary.pdf.rooming_list', VCStaff\ReserveEstimateItinerary\Pdf\RoomingListComposer::class); // ルーミングリストPDF（当該施設当該日）
        View::composer('staff.reserve_itinerary.pdf.itinerary_rooming_list', VCStaff\ReserveEstimateItinerary\Pdf\ItineraryRoomingListComposer::class); // （当該日程における）ルーミングリストPDF

        // 作成・編集ページ共通
        View::composer('staff.reserve_confirm.edit', VCStaff\ReserveConfirm\EditFormComposer::class); // 編集ページ
        View::composer('staff.reserve_confirm.pdf', VCStaff\ReserveConfirm\PdfComposer::class); // PDFページ

        // 請求書
        View::composer('staff.reserve_invoice.edit', VCStaff\ReserveInvoice\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.reserve_invoice.pdf', VCStaff\ReserveInvoice\PdfComposer::class); // PDFページ

        // 一括請求書
        View::composer('staff.reserve_bundle_invoice.edit', VCStaff\ReserveBundleInvoice\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.reserve_bundle_invoice.pdf', VCStaff\ReserveBundleInvoice\PdfComposer::class); // PDFページ

        // 領収書
        View::composer('staff.reserve_receipt.edit', VCStaff\ReserveReceipt\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.reserve_receipt.pdf', VCStaff\ReserveReceipt\PdfComposer::class); // PDFページ

        // 一括領収書
        View::composer('staff.reserve_bundle_receipt.edit', VCStaff\ReserveBundleReceipt\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.reserve_bundle_receipt.pdf', VCStaff\ReserveBundleReceipt\PdfComposer::class); // PDFページ

        // 参加者キャンセルチャージ(ASP用)
        View::composer('staff.participant.cancel_charge', VCStaff\Participant\CancelChargeFormComposer::class); // 作成ページ

        // 予約キャンセルチャージ(ASP用)
        View::composer('staff.reserve.cancel_charge', VCStaff\ReserveEstimate\CancelChargeFormComposer::class); // 作成ページ


        // 経理業務
        View::composer('staff.management_invoice.index', VCStaff\ManagementInvoice\IndexFormComposer::class); // 請求管理一覧
        View::composer('staff.management_invoice.breakdown', VCStaff\ManagementInvoice\BreakdownFormComposer::class); // 一括請求内訳一覧

        // 支払管理
        View::composer('staff.management_payment.reserve', VCStaff\ManagementPayment\ReserveFormComposer::class); // 予約毎一覧
        View::composer('staff.management_payment.item', VCStaff\ManagementPayment\ItemFormComposer::class); // 仕入先＆商品毎一覧
        View::composer('staff.management_payment.detail', VCStaff\ManagementPayment\DetailFormComposer::class); // 商品一覧

        View::composer('staff.user.index', VCStaff\User\IndexFormComposer::class); // 個人顧客一覧
        View::composer('staff.user.show', VCStaff\User\ShowFormComposer::class); // 表示ページ
        View::composer('staff.user.create', VCStaff\User\CreateFormComposer::class); // 個人顧客作成
        View::composer('staff.user.edit', VCStaff\User\EditFormComposer::class); // 個人顧客編集

        View::composer('staff.business_user.index', VCStaff\BusinessUser\IndexFormComposer::class); // 法人顧客一覧
        View::composer('staff.business_user.show', VCStaff\BusinessUser\ShowFormComposer::class); // 表示ページ
        View::composer('staff.business_user.create', VCStaff\BusinessUser\CreateFormComposer::class); // 法人顧客作成
        View::composer('staff.business_user.edit', VCStaff\BusinessUser\EditFormComposer::class); // 法人顧客編集
        
        // メールテンプレート一覧・作成
        View::composer('staff.mail_template.create', VCStaff\MailTemplate\CreateFormComposer::class);
        View::composer('staff.mail_template.edit', VCStaff\MailTemplate\EditFormComposer::class);

        // 帳票設定
        View::composer('staff.document_category.index', VCStaff\DocumentCategory\IndexComposer::class); // 一覧ページ

        View::composer('staff.document_category.document_common.create', VCStaff\DocumentCategory\Common\CreateFormComposer::class); // 共通設定作成
        View::composer('staff.document_category.document_common.edit', VCStaff\DocumentCategory\Common\EditFormComposer::class); // 共通設定編集

        View::composer('staff.document_category.quote.create', VCStaff\DocumentCategory\Quote\CreateFormComposer::class); // 帳票見積/予約確認書設定作成
        View::composer('staff.document_category.quote.edit', VCStaff\DocumentCategory\Quote\EditFormComposer::class); // 帳票見積/予約確認書設定編集

        View::composer('staff.document_category.request.create', VCStaff\DocumentCategory\Request\CreateFormComposer::class); // 請求書作成
        View::composer('staff.document_category.request.edit', VCStaff\DocumentCategory\Request\EditFormComposer::class); // 請求書編集

        View::composer('staff.document_category.request_all.create', VCStaff\DocumentCategory\RequestAll\CreateFormComposer::class); // 請求書一括設定作成
        View::composer('staff.document_category.request_all.edit', VCStaff\DocumentCategory\RequestAll\EditFormComposer::class); // 請求書一括設定編集

        View::composer('staff.document_category.receipt.create', VCStaff\DocumentCategory\Receipt\CreateFormComposer::class); // 領収書設定作成
        View::composer('staff.document_category.receipt.edit', VCStaff\DocumentCategory\Receipt\EditFormComposer::class); // 領収書設定編集

        View::composer('staff.direction.index', VCStaff\Direction\IndexFormComposer::class); // 方面一覧
        View::composer('staff.direction.create', VCStaff\Direction\CreateFormComposer::class); // 方面作成
        View::composer('staff.direction.edit', VCStaff\Direction\EditFormComposer::class); // 方面編集

        View::composer('staff.area.index', VCStaff\Area\IndexFormComposer::class); // 国・地域一覧
        View::composer('staff.area.create', VCStaff\Area\CreateFormComposer::class); // 国・地域作成
        View::composer('staff.area.edit', VCStaff\Area\EditFormComposer::class); // 国・地域編集

        View::composer('staff.city.index', VCStaff\City\IndexFormComposer::class); // 都市・空港一覧
        View::composer('staff.city.create', VCStaff\City\CreateFormComposer::class); // 都市・空港作成
        View::composer('staff.city.edit', VCStaff\City\EditFormComposer::class); // 都市・空港編集

        View::composer('staff.subject.index', VCStaff\Subject\IndexFormComposer::class); // 科目index
        View::composer('staff.subject.create_base', VCStaff\Subject\CreateFormComposer::class); // 科目作成
        View::composer('staff.subject.edit', VCStaff\Subject\EditFormComposer::class); // 科目編集
        View::composer('staff.subject.create.option', VCStaff\Subject\Option\CreateFormComposer::class); // オプション作成
        View::composer('staff.subject.edit.option', VCStaff\Subject\Option\EditFormComposer::class); // オプション編集
        View::composer('staff.subject.create.airplane', VCStaff\Subject\Airplane\CreateFormComposer::class); // 航空券作成
        View::composer('staff.subject.edit.airplane', VCStaff\Subject\Airplane\EditFormComposer::class); // 航空券編集
        View::composer('staff.subject.create.hotel', VCStaff\Subject\Hotel\CreateFormComposer::class); // ホテル作成
        View::composer('staff.subject.edit.hotel', VCStaff\Subject\Hotel\EditFormComposer::class); // ホテル編集

        View::composer('staff.supplier.index', VCStaff\Supplier\IndexFormComposer::class); // 仕入先一覧
        View::composer('staff.supplier.create', VCStaff\Supplier\CreateFormComposer::class); // 仕入先作成
        View::composer('staff.supplier.edit', VCStaff\Supplier\EditFormComposer::class); // 仕入先編集

        View::composer('staff.agency_role.*', VCStaff\AgencyRole\AgencyRoleEditFormComposer::class);// ユーザー権限編集に使用する項目値を提供

        // スタッフ一覧・編集・作成
        View::composer('staff.staff.index', VCStaff\Staff\IndexFormComposer::class);
        View::composer('staff.staff.create', VCStaff\Staff\CreateFormComposer::class);
        View::composer('staff.staff.edit', VCStaff\Staff\EditFormComposer::class);

        // カスタム項目
        View::composer('staff.user_custom_item.index', VCStaff\UserCustomItem\IndexComposer::class);// カスタム項目indexページで使用する項目値を提供
        View::composer('staff.user_custom_item.create.text', VCStaff\UserCustomItem\Text\CreateFormComposer::class);// カスタム項目作成ページ(テキスト項目用)で使用する項目値を提供
        View::composer('staff.user_custom_item.create.list', VCStaff\UserCustomItem\Lists\CreateFormComposer::class);// カスタム項目作成ページ(リスト用)で使用する項目値を提供
        View::composer('staff.user_custom_item.create.date', VCStaff\UserCustomItem\Date\CreateFormComposer::class);// カスタム項目作成ページ(日時用)で使用する項目値を提供
        View::composer('staff.user_custom_item.edit.text', VCStaff\UserCustomItem\Text\EditFormComposer::class);// カスタム項目編集ページ(テキスト項目用)で使用する項目値を提供
        View::composer('staff.user_custom_item.edit.list', VCStaff\UserCustomItem\Lists\EditFormComposer::class);// カスタム項目編集ページ(リスト用)で使用する項目値を提供
        View::composer('staff.user_custom_item.edit.date', VCStaff\UserCustomItem\Date\EditFormComposer::class);// カスタム項目編集ページ(日時項目用)で使用する項目値を提供


        /******
         * HAKKEN Web
         */

        View::composer('staff.web.reserve.index', VCStaff\Web\ReserveEstimate\ReserveIndexFormComposer::class); // 予約一覧
        View::composer('staff.web.estimate.index', VCStaff\Web\ReserveEstimate\EstimateIndexFormComposer::class); // 見積一覧
        View::composer('staff.web.estimate.request', VCStaff\Web\ReserveEstimate\EstimateRequestFormComposer::class); // リクエスト詳細
        // 予約・見積詳細（showは予約・見積・催行済共通）
        View::composers([
            VCStaff\Web\ReserveEstimate\ShowFormComposer::class => [
                'staff.web.reserve.show', 
                'staff.web.estimate.show',
                // 'staff.web.departed.show', ←実装後コメント解除
            ],
        ]);

        // 参加者キャンセルチャージ(WEB用)
        View::composer('staff.web.participant.cancel_charge', VCStaff\Web\Participant\CancelChargeFormComposer::class); // 作成ページ

        // 予約キャンセルチャージ(WEB用)
        View::composer('staff.web.reserve.cancel_charge', VCStaff\Web\ReserveEstimate\CancelChargeFormComposer::class); // 作成ページ


        // 予約・見積編集（editは予約・見積共通）
        View::composers([
            VCStaff\Web\ReserveEstimate\EditFormComposer::class => [
                'staff.web.reserve.edit', 
                'staff.web.estimate.edit',
            ],
        ]);
        // 予約旅程
        // 作成（createは予約・見積もり共通）
        View::composers([
            VCStaff\Web\ReserveEstimateItinerary\CreateFormComposer::class => [
                'staff.web.reserve_itinerary.create', 
                // 'staff.web.estimate_itinerary.create', ⇦使ってない？
            ],
        ]);
        View::composer('staff.web.reserve_itinerary.edit', VCStaff\Web\ReserveEstimateItinerary\EditFormComposer::class); // 編集ページ
        View::composer('staff.web.reserve_itinerary.pdf.itinerary', VCStaff\Web\ReserveEstimateItinerary\Pdf\ItineraryComposer::class); // 行程PDF
        View::composer('staff.web.reserve_itinerary.pdf.rooming_list', VCStaff\Web\ReserveEstimateItinerary\Pdf\RoomingListComposer::class); // ルーミングリストPDF（当該施設当該日）
        View::composer('staff.web.reserve_itinerary.pdf.itinerary_rooming_list', VCStaff\Web\ReserveEstimateItinerary\Pdf\ItineraryRoomingListComposer::class); // （当該日程における）ルーミングリストPDF

        // 作成・編集ページ共通
        View::composer('staff.web.reserve_confirm.edit', VCStaff\Web\ReserveConfirm\EditFormComposer::class); // 編集ページ
        View::composer('staff.web.reserve_confirm.pdf', VCStaff\Web\ReserveConfirm\PdfComposer::class); // PDFページ

        // 請求書
        View::composer('staff.web.reserve_invoice.edit', VCStaff\Web\ReserveInvoice\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.web.reserve_invoice.pdf', VCStaff\Web\ReserveInvoice\PdfComposer::class); // PDFページ

        // 領収書
        View::composer('staff.web.reserve_receipt.edit', VCStaff\Web\ReserveReceipt\EditFormComposer::class); // 作成・編集ページ
        View::composer('staff.web.reserve_receipt.pdf', VCStaff\Web\ReserveReceipt\PdfComposer::class); // PDFページ

        View::composer('staff.web.company.edit', VCStaff\Web\Company\EditFormComposer::class); // 会社情報
        View::composer('staff.web.profile.edit', VCStaff\Web\Profile\EditFormComposer::class); // プロフィール情報
        View::composer('staff.web.profile.preview', VCStaff\Web\Profile\PreviewFormComposer::class); // プロフィールプレビュー

        // モデルコース
        View::composer('staff.web.modelcourse.index', VCStaff\Web\Modelcourse\IndexFormComposer::class); // index
        View::composer('staff.web.modelcourse.show', VCStaff\Web\Modelcourse\ShowFormComposer::class); // show
        View::composer('staff.web.modelcourse.preview', VCStaff\Web\Modelcourse\PreviewFormComposer::class); // プレビュー
        // 作成、編集ページ共通
        View::composers([
            VCStaff\Web\Modelcourse\EditFormComposer::class => [
                'staff.web.modelcourse.create', 
                'staff.web.modelcourse.edit',
            ],
        ]);

    }
}
