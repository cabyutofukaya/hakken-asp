<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Repositories\ReserveReceiptInvoice\ReserveReceiptInvoiceRepositoryInterface::class,
            \App\Repositories\ReserveReceiptInvoice\ReserveReceiptInvoiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveBundleReceiptInvoice\ReserveBundleReceiptInvoiceRepositoryInterface::class,
            \App\Repositories\ReserveBundleReceiptInvoice\ReserveBundleReceiptInvoiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyDeposit\AgencyDepositRepositoryInterface::class,
            \App\Repositories\AgencyDeposit\AgencyDepositRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyBundleDeposit\AgencyBundleDepositRepositoryInterface::class,
            \App\Repositories\AgencyBundleDeposit\AgencyBundleDepositRepository::class
        );
        $this->app->bind(
            \App\Repositories\DocumentPdf\DocumentPdfRepositoryInterface::class,
            \App\Repositories\DocumentPdf\DocumentPdfRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveBundleInvoice\ReserveBundleInvoiceRepositoryInterface::class,
            \App\Repositories\ReserveBundleInvoice\ReserveBundleInvoiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyBundleDepositCustomValue\AgencyBundleDepositCustomValueRepositoryInterface::class,
            \App\Repositories\AgencyBundleDepositCustomValue\AgencyBundleDepositCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyDepositCustomValue\AgencyDepositCustomValueRepositoryInterface::class,
            \App\Repositories\AgencyDepositCustomValue\AgencyDepositCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveInvoiceSequence\ReserveInvoiceSequenceRepositoryInterface::class,
            \App\Repositories\ReserveInvoiceSequence\ReserveInvoiceSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveReceiptSequence\ReserveReceiptSequenceRepositoryInterface::class,
            \App\Repositories\ReserveReceiptSequence\ReserveReceiptSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyConsultationSequence\AgencyConsultationSequenceRepositoryInterface::class,
            \App\Repositories\AgencyConsultationSequence\AgencyConsultationSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyConsultation\AgencyConsultationRepositoryInterface::class,
            \App\Repositories\AgencyConsultation\AgencyConsultationRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyConsultationCustomValue\AgencyConsultationCustomValueRepositoryInterface::class,
            \App\Repositories\AgencyConsultationCustomValue\AgencyConsultationCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveSequence\ReserveSequenceRepositoryInterface::class,
            \App\Repositories\ReserveSequence\ReserveSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebReserveSequence\WebReserveSequenceRepositoryInterface::class,
            \App\Repositories\WebReserveSequence\WebReserveSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\EstimateSequence\EstimateSequenceRepositoryInterface::class,
            \App\Repositories\EstimateSequence\EstimateSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebEstimateSequence\WebEstimateSequenceRepositoryInterface::class,
            \App\Repositories\WebEstimateSequence\WebEstimateSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveItinerary\ReserveItineraryRepositoryInterface::class,
            \App\Repositories\ReserveItinerary\ReserveItineraryRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveSchedule\ReserveScheduleRepositoryInterface::class,
            \App\Repositories\ReserveSchedule\ReserveScheduleRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveSchedulePhoto\ReserveSchedulePhotoRepositoryInterface::class,
            \App\Repositories\ReserveSchedulePhoto\ReserveSchedulePhotoRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveTravelDate\ReserveTravelDateRepositoryInterface::class,
            \App\Repositories\ReserveTravelDate\ReserveTravelDateRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubject\ReservePurchasingSubjectRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubject\ReservePurchasingSubjectRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectOption\ReservePurchasingSubjectOptionRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectOption\ReservePurchasingSubjectOptionRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectHotel\ReservePurchasingSubjectHotelRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectHotel\ReservePurchasingSubjectHotelRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectAirplaneCustomValue\ReservePurchasingSubjectAirplaneCustomValueRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectAirplaneCustomValue\ReservePurchasingSubjectAirplaneCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectHotelCustomValue\ReservePurchasingSubjectHotelCustomValueRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectHotelCustomValue\ReservePurchasingSubjectHotelCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectAirplane\ReservePurchasingSubjectAirplaneRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectAirplane\ReservePurchasingSubjectAirplaneRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveParticipantOptionPrice\ReserveParticipantOptionPriceRepositoryInterface::class,
            \App\Repositories\ReserveParticipantOptionPrice\ReserveParticipantOptionPriceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveParticipantHotelPrice\ReserveParticipantHotelPriceRepositoryInterface::class,
            \App\Repositories\ReserveParticipantHotelPrice\ReserveParticipantHotelPriceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveParticipantAirplanePrice\ReserveParticipantAirplanePriceRepositoryInterface::class,
            \App\Repositories\ReserveParticipantAirplanePrice\ReserveParticipantAirplanePriceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AccountPayable\AccountPayableRepositoryInterface::class,
            \App\Repositories\AccountPayable\AccountPayableRepository::class
        );
        $this->app->bind(
            \App\Repositories\AccountPayableReserve\AccountPayableReserveRepositoryInterface::class,
            \App\Repositories\AccountPayableReserve\AccountPayableReserveRepository::class
        );
        $this->app->bind(
            \App\Repositories\AccountPayableItem\AccountPayableItemRepositoryInterface::class,
            \App\Repositories\AccountPayableItem\AccountPayableItemRepository::class
        );
        $this->app->bind(
            \App\Repositories\AccountPayableDetail\AccountPayableDetailRepositoryInterface::class,
            \App\Repositories\AccountPayableDetail\AccountPayableDetailRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyWithdrawalItemHistory\AgencyWithdrawalItemHistoryRepositoryInterface::class,
            \App\Repositories\AgencyWithdrawalItemHistory\AgencyWithdrawalItemHistoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepositoryRepositoryInterface::class,
            \App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepositoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyWithdrawalItemHistoryCustomValue\AgencyWithdrawalItemHistoryCustomValueRepositoryRepositoryInterface::class,
            \App\Repositories\AgencyWithdrawalItemHistoryCustomValue\AgencyWithdrawalItemHistoryCustomValueRepositoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyWithdrawalCustomValue\AgencyWithdrawalCustomValueRepositoryInterface::class,
            \App\Repositories\AgencyWithdrawalCustomValue\AgencyWithdrawalCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReservePurchasingSubjectOptionCustomValue\ReservePurchasingSubjectOptionCustomValueRepositoryInterface::class,
            \App\Repositories\ReservePurchasingSubjectOptionCustomValue\ReservePurchasingSubjectOptionCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserSequence\UserSequenceRepositoryInterface::class,
            \App\Repositories\UserSequence\UserSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\BusinessUserSequence\BusinessUserSequenceRepositoryInterface::class,
            \App\Repositories\BusinessUserSequence\BusinessUserSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\BusinessUserManagerSequence\BusinessUserManagerSequenceRepositoryInterface::class,
            \App\Repositories\BusinessUserManagerSequence\BusinessUserManagerSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserConsultationSequence\UserConsultationSequenceRepositoryInterface::class,
            \App\Repositories\UserConsultationSequence\UserConsultationSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\Reserve\ReserveRepositoryInterface::class,
            \App\Repositories\Reserve\ReserveRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebReserve\WebReserveRepositoryInterface::class,
            \App\Repositories\WebReserve\WebReserveRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveDeparted\ReserveDepartedRepositoryInterface::class,
            \App\Repositories\ReserveDeparted\ReserveDepartedRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebReserveExt\WebReserveExtRepositoryInterface::class,
            \App\Repositories\WebReserveExt\WebReserveExtRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebMessage\WebMessageRepositoryInterface::class,
            \App\Repositories\WebMessage\WebMessageRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebMessageHistory\WebMessageHistoryRepositoryInterface::class,
            \App\Repositories\WebMessageHistory\WebMessageHistoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebOnlineSchedule\WebOnlineScheduleRepositoryInterface::class,
            \App\Repositories\WebOnlineSchedule\WebOnlineScheduleRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveCustomValue\ReserveCustomValueRepositoryInterface::class,
            \App\Repositories\ReserveCustomValue\ReserveCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveConfirm\ReserveConfirmRepositoryInterface::class,
            \App\Repositories\ReserveConfirm\ReserveConfirmRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveConfirmUser\ReserveConfirmUserRepositoryInterface::class,
            \App\Repositories\ReserveConfirmUser\ReserveConfirmUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\ReserveConfirmBusinessUserManager\ReserveConfirmBusinessUserManagerRepositoryInterface::class,
            \App\Repositories\ReserveConfirmBusinessUserManager\ReserveConfirmBusinessUserManagerRepository::class
        );
        $this->app->bind(
            \App\Repositories\BusinessUser\BusinessUserRepositoryInterface::class,
            \App\Repositories\BusinessUser\BusinessUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\BusinessUserManager\BusinessUserManagerRepositoryInterface::class,
            \App\Repositories\BusinessUserManager\BusinessUserManagerRepository::class
        );
        $this->app->bind(
            \App\Repositories\BusinessUserCustomValue\BusinessUserCustomValueRepositoryInterface::class,
            \App\Repositories\BusinessUserCustomValue\BusinessUserCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\Prefecture\PrefectureRepositoryInterface::class,
            \App\Repositories\Prefecture\PrefectureRepository::class
        );
        $this->app->bind(
            \App\Repositories\Country\CountryRepositoryInterface::class,
            \App\Repositories\Country\CountryRepository::class
        );
        $this->app->bind(
            \App\Repositories\Inflow\InflowInterface::class,
            \App\Repositories\Inflow\InflowRepository::class
        );
        $this->app->bind(
            \App\Repositories\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\AspUser\AspUserRepositoryInterface::class,
            \App\Repositories\AspUser\AspUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\AspUserExt\AspUserExtRepositoryInterface::class,
            \App\Repositories\AspUserExt\AspUserExtRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserVisa\UserVisaRepositoryInterface::class,
            \App\Repositories\UserVisa\UserVisaRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserMileage\UserMileageRepositoryInterface::class,
            \App\Repositories\UserMileage\UserMileageRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserMileageCustomValue\UserMileageCustomValueRepositoryInterface::class,
            \App\Repositories\UserMileageCustomValue\UserMileageCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserMemberCard\UserMemberCardRepositoryInterface::class,
            \App\Repositories\UserMemberCard\UserMemberCardRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserCustomValue\UserCustomValueRepositoryInterface::class,
            \App\Repositories\UserCustomValue\UserCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\Chat\ChatRepositoryInterface::class,
            \App\Repositories\Chat\ChatRepository::class
        );
        $this->app->bind(
            \App\Repositories\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserSequence\UserSequenceRepositoryInterface::class,
            \App\Repositories\UserSequence\UserSequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencySequence\AgencySequenceRepositoryInterface::class,
            \App\Repositories\AgencySequence\AgencySequenceRepository::class
        );
        $this->app->bind(
            \App\Repositories\ActLog\ActLogRepositoryInterface::class,
            \App\Repositories\ActLog\ActLogRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contract\ContractRepositoryInterface::class,
            \App\Repositories\Contract\ContractRepository::class
        );
        $this->app->bind(
            \App\Repositories\ContractPlan\ContractPlanRepositoryInterface::class,
            \App\Repositories\ContractPlan\ContractPlanRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyRole\AgencyRoleRepositoryInterface::class,
            \App\Repositories\AgencyRole\AgencyRoleRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserCustomCategory\UserCustomCategoryRepositoryInterface::class,
            \App\Repositories\UserCustomCategory\UserCustomCategoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\UserCustomCategoryItem\UserCustomCategoryItemRepositoryInterface::class,
            \App\Repositories\UserCustomCategoryItem\UserCustomCategoryItemRepository::class
        );
        $this->app->bind(
            \App\Repositories\StaffCustomValue\StaffCustomValueRepositoryInterface::class,
            \App\Repositories\StaffCustomValue\StaffCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\MailTemplate\MailTemplateRepositoryInterface::class,
            \App\Repositories\MailTemplate\MailTemplateRepository::class
        );
        $this->app->bind(
            \App\Repositories\DocumentCategory\DocumentCategoryRepositoryInterface::class,
            \App\Repositories\DocumentCategory\DocumentCategoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\DocumentCommon\DocumentCommonRepositoryInterface::class,
            \App\Repositories\DocumentCommon\DocumentCommonRepository::class
        );
        $this->app->bind(
            \App\Repositories\DocumentQuote\DocumentQuoteRepositoryInterface::class,
            \App\Repositories\DocumentQuote\DocumentQuoteRepository::class
        );
        $this->app->bind(
            \App\Repositories\DocumentRequest\DocumentRequestRepositoryInterface::class,
            \App\Repositories\DocumentRequest\DocumentRequestRepository::class
        );
        $this->app->bind(
            \App\Repositories\Direction\DirectionRepositoryInterface::class,
            \App\Repositories\Direction\DirectionRepository::class
        );
        $this->app->bind(
            \App\Repositories\Area\AreaRepositoryInterface::class,
            \App\Repositories\Area\AreaRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectOption\SubjectOptionRepositoryInterface::class,
            \App\Repositories\SubjectOption\SubjectOptionRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectAirplane\SubjectAirplaneRepositoryInterface::class,
            \App\Repositories\SubjectAirplane\SubjectAirplaneRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectHotel\SubjectHotelRepositoryInterface::class,
            \App\Repositories\SubjectHotel\SubjectHotelRepository::class
        );
        $this->app->bind(
            \App\Repositories\SupplierPaymentDate\SupplierPaymentDateRepositoryInterface::class,
            \App\Repositories\SupplierPaymentDate\SupplierPaymentDateRepository::class
        );
        $this->app->bind(
            \App\Repositories\Supplier\SupplierRepositoryInterface::class,
            \App\Repositories\Supplier\SupplierRepository::class
        );
        $this->app->bind(
            \App\Repositories\SupplierCustomValue\SupplierCustomValueRepositoryInterface::class,
            \App\Repositories\SupplierCustomValue\SupplierCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectOptionCustomValue\SubjectOptionCustomValueRepositoryInterface::class,
            \App\Repositories\SubjectOptionCustomValue\SubjectOptionCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectAirpaneCustomValue\SubjectArplaneCustomValueRepositoryInterface::class,
            \App\Repositories\SubjectAirplaneCustomValue\SubjectAirplaneCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\SubjectHotelCustomValue\SubjectHotelCustomValueRepositoryInterface::class,
            \App\Repositories\SubjectHotelCustomValue\SubjectHotelCustomValueRepository::class
        );
        $this->app->bind(
            \App\Repositories\Bank\BankRepositoryInterface::class,
            \App\Repositories\Bank\BankRepository::class
        );
        $this->app->bind(
            \App\Repositories\SupplierAccountPayable\SupplierAccountPayableRepositoryInterface::class,
            \App\Repositories\SupplierAccountPayable\SupplierAccountPayableRepository::class
        );
        $this->app->bind(
            \App\Repositories\SystemNews\SystemNewsRepositoryInterface::class,
            \App\Repositories\SystemNews\SystemNewsRepository::class
        );
        $this->app->bind(
            \App\Repositories\AgencyNotification\AgencyNotificationRepositoryInterface::class,
            \App\Repositories\AgencyNotification\AgencyNotificationRepository::class
        );
        $this->app->bind(
            \App\Repositories\PriceRelatedChange\PriceRelatedChangeRepositoryInterface::class,
            \App\Repositories\PriceRelatedChange\PriceRelatedChangeRepository::class
        );

        // HAKKEN??????
        // $this->app->bind(
        //     \App\Repositories\WebUserSequence\WebUserSequenceRepositoryInterface::class,
        //     \App\Repositories\WebUserSequence\WebUserSequenceRepository::class
        // );
        $this->app->bind(
            \App\Repositories\BaseWebUser\BaseWebUserRepositoryInterface::class,
            \App\Repositories\BaseWebUser\BaseWebUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebUser\WebUserRepositoryInterface::class,
            \App\Repositories\WebUser\WebUserRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebCompany\WebCompanyRepositoryInterface::class,
            \App\Repositories\WebCompany\WebCompanyRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebProfile\WebProfileRepositoryInterface::class,
            \App\Repositories\WebProfile\WebProfileRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebProfileTag\WebProfileTagRepositoryInterface::class,
            \App\Repositories\WebProfileTag\WebProfileTagRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebProfileProfilePhoto\WebProfileProfilePhotoRepositoryInterface::class,
            \App\Repositories\WebProfileProfilePhoto\WebProfileProfilePhotoRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebProfileCoverPhoto\WebProfileCoverPhotoRepositoryInterface::class,
            \App\Repositories\WebProfileCoverPhoto\WebProfileCoverPhotoRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebModelcourse\WebModelcourseRepositoryInterface::class,
            \App\Repositories\WebModelcourse\WebModelcourseRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebModelcourseTag\WebModelcourseTagRepositoryInterface::class,
            \App\Repositories\WebModelcourseTag\WebModelcourseTagRepository::class
        );
        $this->app->bind(
            \App\Repositories\WebModelcoursePhoto\WebModelcoursePhotoRepositoryInterface::class,
            \App\Repositories\WebModelcoursePhoto\WebModelcoursePhotoRepository::class
        );
        $this->app->bind(
            \App\Repositories\ZoomApiKey\ZoomApiKeyRepositoryInterface::class,
            \App\Repositories\ZoomApiKey\ZoomApiKeyRepository::class
        );


        $this->app->bind(
            \App\Repositories\Purpose\PurposeRepositoryInterface::class,
            \App\Repositories\Purpose\PurposeRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interest\InterestRepositoryInterface::class,
            \App\Repositories\Interest\InterestRepository::class
        );

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
