import React, { useState } from "react";
import EmcontactModal from "./EmcontactModal";

// 携帯番号、固定電話入力コンポーネント
const PhoneRow = ({
    label,
    name,
    columnName,
    value,
    placeholder,
    maxlength,
    emergencyContact,
    setSelectedEmergencyContact
}) => (
    <>
        <span className="inputLabel">
            {label}
            {emergencyContact === columnName && (
                <span className="default">(緊急連絡先)</span>
            )}
            {emergencyContact !== columnName && (
                <a
                    className="js-modal-open"
                    data-target="mdEmcontact"
                    onClick={e => setSelectedEmergencyContact(columnName)}
                >
                    <span className="material-icons">error</span>
                    緊急連絡先に設定
                </a>
            )}
        </span>
        <input
            type="tel"
            name={name}
            value={value}
            placeholder={placeholder}
            maxLength={maxlength}
            disabled={true}
        />
    </>
);

const DisableContactInputArea = ({ defaultValue }) => {
    // form初期入力値
    const initialValue = {
        userable: {
            mobile_phone: defaultValue?.userable?.mobile_phone ?? "",
            tel: defaultValue?.userable?.tel ?? "",
            fax: defaultValue?.userable?.fax ?? "",
            email: defaultValue?.userable?.email ?? "",
            user_ext: {
                emergency_contact_column:
                    defaultValue?.userable?.user_ext
                        ?.emergency_contact_column ?? ""
            }
        }
    };

    const [input, setInput] = useState(initialValue); // form入力値
    const [selectedEmergencyContact, setSelectedEmergencyContact] = useState(
        ""
    ); // 「緊急連絡先に設定」をクリックしたフィールドを特定するための変数

    /**
     * 「緊急連絡先に設定」OK時のイベント
     *
     * emergency_contact_columnフィールドにselectedEmergencyContactの値をセット
     */
    const handleChangeEmergencyContact = e => {
        e.preventDefault();
        $(".js-modal-close").trigger("click"); // モーダルclose

        let data = _.cloneDeep(input);
        _.set(
            data,
            "userable.user_ext.emergency_contact_column",
            selectedEmergencyContact
        );

        setInput({ ...data });
    };

    return (
        <>
            <li>
                <PhoneRow
                    label="携帯"
                    name="userable[mobile_phone]"
                    columnName="mobile_phone"
                    value={input.userable.mobile_phone ?? ""}
                    placeholder="例）090-1111-1111"
                    maxlength={32}
                    emergencyContact={
                        input.userable.user_ext.emergency_contact_column
                    }
                    setSelectedEmergencyContact={setSelectedEmergencyContact}
                />
            </li>
            <li>
                <PhoneRow
                    label="固定電話"
                    name="userable[tel]"
                    columnName="tel"
                    value={input.userable.tel ?? ""}
                    placeholder="例）03-1111-1111"
                    maxlength={32}
                    emergencyContact={
                        input.userable.user_ext.emergency_contact_column
                    }
                    setSelectedEmergencyContact={setSelectedEmergencyContact}
                />
            </li>
            <li>
                <span className="inputLabel">FAX</span>
                <input
                    type="tel"
                    name="userable[fax]"
                    value={input.userable.fax ?? ""}
                    placeholder="例）03-1111-1111"
                    maxLength="32"
                    disabled={true}
                />
            </li>
            <li>
                <span className="inputLabel">メールアドレス</span>
                <input
                    type="email"
                    name="userable[email]"
                    value={input.userable.email ?? ""}
                    placeholder="例）yamada@cab-station.com"
                    maxLength="100"
                    disabled={true}
                />
            </li>
            {/* hiddenフィールド。緊急連絡先に設定したカラムをセット */}
            <input
                type="hidden"
                name="userable[user_ext][emergency_contact_column]"
                value={input.userable.user_ext.emergency_contact_column ?? ""}
            />
            {/* 緊急連絡先設定確認ダイアログ */}
            <EmcontactModal handleClick={handleChangeEmergencyContact} />
        </>
    );
};

export default DisableContactInputArea;
