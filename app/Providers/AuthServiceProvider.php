<?php

namespace App\Providers;

use Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // cabマスター
        'App\Models\MasterDirection' => 'App\Policies\MasterDirectionPolicy',
        'App\Models\MasterArea' => 'App\Policies\MasterAreaPolicy',
        'App\Models\SystemNews' => 'App\Policies\SystemNewsPolicy',
        //
        'App\Models\AgencyConsultation' => 'App\Policies\AgencyConsultationPolicy',
        'App\Models\Reserve' => 'App\Policies\ReservePolicy',
        'App\Models\WebReserveExt' => 'App\Policies\WebReserveExtPolicy',
        'App\Models\ReserveItinerary' => 'App\Policies\ReserveItineraryPolicy',
        'App\Models\ReserveConfirm' => 'App\Policies\ReserveConfirmPolicy',
        'App\Models\AccountPayable' => 'App\Policies\AccountPayablePolicy',
        'App\Models\AccountPayableDetail' => 'App\Policies\AccountPayableDetailPolicy',
        'App\Models\AgencyWithdrawal' => 'App\Policies\AgencyWithdrawalPolicy',
        'App\Models\VReserveInvoice' => 'App\Policies\VReserveInvoicePolicy',
        'App\Models\AgencyDeposit' => 'App\Policies\AgencyDepositPolicy',
        'App\Models\AgencyBundleDeposit' => 'App\Policies\AgencyBundleDepositPolicy',
        'App\Models\Participant' => 'App\Policies\ParticipantPolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\UserVisa' => 'App\Policies\UserVisaPolicy',
        'App\Models\UserMileage' => 'App\Policies\UserMileagePolicy',
        'App\Models\UserMemberCard' => 'App\Policies\UserMemberCardPolicy',
        'App\Models\BusinessUser' => 'App\Policies\BusinessUserPolicy',
        'App\Models\BusinessUserManager' => 'App\Policies\BusinessUserManagerPolicy',
        'App\Models\Agency' => 'App\Policies\AgencyPolicy',
        'App\Models\Staff' => 'App\Policies\StaffPolicy',
        'App\Models\Suggestion' => 'App\Policies\SuggestionPolicy',
        'App\Models\Purpose' => 'App\Policies\PurposePolicy',
        'App\Models\Interest' => 'App\Policies\InterestPolicy',
        'App\Models\Role' => 'App\Policies\RolePolicy',
        'App\Models\AgencyRole' => 'App\Policies\AgencyRolePolicy',
        'App\Models\UserCustomItem' => 'App\Policies\UserCustomItemPolicy',
        'App\Models\MailTemplate' => 'App\Policies\MailTemplatePolicy',
        'App\Models\Bank' => 'App\Policies\BankPolicy',
        // 帳票設定
        'App\Models\DocumentCategory' => 'App\Policies\DocumentCategoryPolicy',
        'App\Models\DocumentCommon' => 'App\Policies\DocumentCommonPolicy',
        'App\Models\DocumentQuote' => 'App\Policies\DocumentQuotePolicy',
        'App\Models\DocumentRequest' => 'App\Policies\DocumentRequestPolicy',
        'App\Models\DocumentRequestAll' => 'App\Policies\DocumentRequestAllPolicy',
        'App\Models\DocumentReceipt' => 'App\Policies\DocumentReceiptPolicy',
        // マスタ設定
        'App\Models\AgencyDirection' => 'App\Policies\AgencyDirectionPolicy',
        // 'App\Models\Direction' => 'App\Policies\DirectionPolicy',
        // 'App\Models\VDirection' => 'App\Policies\VDirectionPolicy',
        // 'App\Models\Area' => 'App\Policies\AreaPolicy',
        'App\Models\AgencyArea' => 'App\Policies\AgencyAreaPolicy',
        'App\Models\City' => 'App\Policies\CityPolicy',
        'App\Models\SubjectOption' => 'App\Policies\SubjectOptionPolicy',
        'App\Models\SubjectAirplane' => 'App\Policies\SubjectAirplanePolicy',
        'App\Models\SubjectHotel' => 'App\Policies\SubjectHotelPolicy',
        'App\Models\Supplier' => 'App\Policies\SupplierPolicy',
        //
        'App\Models\ReserveConfirm' => 'App\Policies\ReserveConfirmPolicy',
        'App\Models\ReserveInvoice' => 'App\Policies\ReserveInvoicePolicy',
        'App\Models\ReserveBundleInvoice' => 'App\Policies\ReserveBundleInvoicePolicy',
        'App\Models\ReserveReceipt' => 'App\Policies\ReserveReceiptPolicy',
        'App\Models\ReserveBundleReceipt' => 'App\Policies\ReserveBundleReceiptPolicy',
        //
        'App\Models\WebUser' => 'App\Policies\WebUserPolicy',
        'App\Models\WebCompany' => 'App\Policies\WebCompanyPolicy',
        'App\Models\WebProfile' => 'App\Policies\WebProfilePolicy',
        'App\Models\WebModelcourse' => 'App\Policies\WebModelcoursePolicy',
        'App\Models\WebOnlineSchedule' => 'App\Policies\WebOnlineSchedulePolicy',
        'App\Models\WebMessage' => 'App\Policies\WebMessagePolicy',
        'App\Models\WebMessageHistory' => 'App\Policies\WebMessageHistoryPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // staff用のカスタマイズ認証
        Auth::provider('staff_auth', function ($app, array $config) {
            return new StaffAuthServiceProvider($this->app['hash'], $config['model']);
        });
    }
}
