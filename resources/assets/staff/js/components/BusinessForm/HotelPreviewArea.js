import React from "react";

/**
 * 宿泊施設連絡先エリア
 *
 * @param {*} param0
 * @returns
 */
const HotelPreviewArea = ({ participantIds, hotelContacts }) => {
    {
        /**表示対象になっていない参加者を宿泊情報から除く */
    }
    const copyObj = _.cloneDeep(hotelContacts);
    Object.keys(copyObj).forEach((k, i) => {
        const ary = copyObj[k]["guests"].filter(item =>
            participantIds.includes(parseInt(item.participant_id, 10))
        );
        copyObj[k]["guests"] = ary;
    });
    return (
        <>
            <h3>宿泊施設連絡先</h3>
            {Object.keys(copyObj).map((key, index) => {
                {
                    /**利用者があれば表示 */
                }
                return Object.keys(copyObj[key].guests).length > 0 ? (
                    <div className="dispHotelList" key={index}>
                        <p className="hotelName">
                            {copyObj[key]["hotel_name"]}
                        </p>
                        <p className="hotelAdd">{copyObj[key]["address"]}</p>
                        <p className="hotelContact">
                            {copyObj[key]?.["tel"] && (
                                <>TEL:{copyObj[key]["tel"]}</>
                            )}
                            {copyObj[key]?.["tel"] && copyObj[key]?.["fax"] && (
                                <> / </>
                            )}
                            {copyObj[key]?.["fax"] && (
                                <>FAX:{copyObj[key]?.["fax"]}</>
                            )}
                        </p>
                        <p className="hotelUrl">
                            {copyObj[key]?.["url"] ?? ""}
                        </p>
                    </div>
                ) : null;
            })}
        </>
    );
};

export default HotelPreviewArea;
