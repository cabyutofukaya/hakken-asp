const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.react("resources/js/app.js", "public/js")
    // .react("resources/assets/staff/js/app.js", "public/staff/js")
    //admin
    .js("resources/assets/admin/js/staff-create.js", "public/admin/js")
    .js("resources/assets/admin/js/user-create.js", "public/admin/js")
    .js("resources/assets/admin/js/users-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/agencies-create.js", "public/admin/js")
    .js("resources/assets/admin/js/agencies-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/purposes-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/interests-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/staffs-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/roles-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/inflow-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/model_log-index.js", "public/admin/js")
    .js("resources/assets/admin/js/web_user-index.js", "public/admin/js")
    .js("resources/assets/admin/js/web_user-edit.js", "public/admin/js")
    .js("resources/assets/admin/js/address_search.js", "public/admin/js")
    .js("resources/assets/admin/js/sortable.js", "public/admin/js")
    .js("resources/assets/admin/js/common.js", "public/admin/js") // jqueryなどの共通スクリプト
    //user
    .sass("resources/assets/admin/sass/app.scss", "public/admin/css")
    //staff
    // .sass("resources/assets/staff/sass/app.scss", "public/staff/css")
    .js("resources/assets/staff/js/common.js", "public/staff/js") // jqueryなどの共通スクリプト
    .js("resources/assets/staff/js/news.js", "public/staff/js")
    .js("resources/assets/staff/js/staff-index.js", "public/staff/js")
    .js("resources/assets/staff/js/agency_role-index.js", "public/staff/js")
    .js("resources/assets/staff/js/agency_role-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/agency_role-create.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/user_custom_item-create_text.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/user_custom_item-index.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/user_custom_item-create_date.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/user_custom_item-create_list.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/user_custom_item-edit_list.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/document_category-index.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/document-setting_row.js", "public/staff/js")
    .js("resources/assets/staff/js/staff-create.js", "public/staff/js")
    .js("resources/assets/staff/js/staff-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/mail_template-index.js", "public/staff/js")
    .js("resources/assets/staff/js/mail_template-create.js", "public/staff/js")
    .js("resources/assets/staff/js/mail_template-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/document_quote-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/document_quote-create.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/document_request-create.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/document_request-edit.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/document_request_all-create.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/document_request_all-edit.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/direction-index.js", "public/staff/js")
    .js("resources/assets/staff/js/area-index.js", "public/staff/js")
    .js("resources/assets/staff/js/area-create-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/city-index.js", "public/staff/js")
    .js("resources/assets/staff/js/city-create-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/supplier-index.js", "public/staff/js")
    .js("resources/assets/staff/js/supplier-create.js", "public/staff/js")
    .js("resources/assets/staff/js/supplier-edit.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/subject_category-index.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/subject-create.js", "public/staff/js")
    .js("resources/assets/staff/js/subject_option-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/subject_airplane-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/subject_hotel-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/user-index.js", "public/staff/js")
    .js("resources/assets/staff/js/user-create.js", "public/staff/js")
    .js("resources/assets/staff/js/user-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/user-show.js", "public/staff/js")
    .js("resources/assets/staff/js/business_user-index.js", "public/staff/js")
    .js("resources/assets/staff/js/business_user-create.js", "public/staff/js")
    .js("resources/assets/staff/js/business_user-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/business_user-show.js", "public/staff/js")
    .js("resources/assets/staff/js/reserve-create.js", "public/staff/js")
    .js("resources/assets/staff/js/reserve-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/reserve-index.js", "public/staff/js")
    .js("resources/assets/staff/js/reserve-show.js", "public/staff/js")
    .js("resources/assets/staff/js/estimate-index.js", "public/staff/js")
    .js("resources/assets/staff/js/estimate-create.js", "public/staff/js")
    .js("resources/assets/staff/js/estimate-edit.js", "public/staff/js")
    .js("resources/assets/staff/js/estimate-show.js", "public/staff/js")
    .js("resources/assets/staff/js/departed-index.js", "public/staff/js")
    .js("resources/assets/staff/js/consultation-index.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/consultation_message-index.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_itinerary-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_confirm-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_invoice-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_bundle_invoice-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_receipt-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_bundle_receipt-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/reserve_cancel_charge-create.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/participant_cancel_charge-create.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/management_invoice-index.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/management_invoice-breakdown.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/management_payment-index.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/react-js/home/index.js",
        "public/staff/js/home-index.js"
    )
    .js(
        "resources/assets/staff/react-js/individual/index.js",
        "public/staff/js/individual-index.js"
    )
    .js("resources/assets/staff/js/web-estimate-index.js", "public/staff/js")
    .js("resources/assets/staff/js/web-reserve-index.js", "public/staff/js")
    .js("resources/assets/staff/js/web-estimate-request.js", "public/staff/js")
    .js("resources/assets/staff/js/web-estimate-edit.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/web_company-create-edit.js",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/web_profile-create-edit.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/web_modelcourse-show.js", "public/staff/js")
    .js("resources/assets/staff/js/web_modelcourse-index.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/web_modelcourse-create-edit.js",
        "public/staff/js"
    )
    .js("resources/assets/staff/js/web-estimate-show.js", "public/staff/js")
    .js("resources/assets/staff/js/web-reserve-show.js", "public/staff/js")
    .js("resources/assets/staff/js/web-reserve-edit.js", "public/staff/js")
    .js(
        "resources/assets/staff/js/web-reserve_cancel_charge-create",
        "public/staff/js"
    )
    .js(
        "resources/assets/staff/js/web-participant_cancel_charge-create.js",
        "public/staff/js"
    )
    .extract(["jquery", "lodash"]);
