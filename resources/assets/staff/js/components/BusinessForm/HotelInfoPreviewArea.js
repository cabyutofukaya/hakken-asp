import React from "react";

const HotelInfoPreviewArea = ({ participantIds, hotelInfo }) => {
    {
        /**表示対象になっていない参加者を宿泊情報から除く */
    }
    const copyObj = _.cloneDeep(hotelInfo);
    Object.keys(copyObj).forEach((date, i) => {
        Object.keys(copyObj[date]).forEach((r, j) => {
            Object.keys(copyObj[date][r]["rooms"]).forEach((s, k) => {
                const ary = copyObj[date][r]["rooms"][s].filter(item =>
                    participantIds.includes(parseInt(item.participant_id, 10))
                );
                if (ary.length === 0) {
                    // 利用者のいない部屋情報は削除
                    delete copyObj[date][r]["rooms"][s];
                } else {
                    copyObj[date][r]["rooms"][s] = ary;
                }
            });
        });
    });
    return (
        <>
            <h3>宿泊施設情報</h3>
            <table>
                <thead>
                    <tr>
                        <th>宿泊日</th>
                        <th>ホテル名</th>
                        <th>部屋タイプ</th>
                        <th>数量</th>
                    </tr>
                </thead>
                <tbody>
                    {Object.keys(copyObj).map((date, i) => {
                        return Object.keys(copyObj[date]).map((k, j) => {
                            {
                                /**宿泊部屋があれば表示 */
                            }
                            return Object.keys(copyObj[date][k].rooms).length >
                                0 ? (
                                <tr key={`${i}_${j}`}>
                                    <td>{date}</td>
                                    <td>
                                        {copyObj[date][k]?.hotel_name ?? ""}
                                    </td>
                                    <td>{copyObj[date][k]?.room_type ?? ""}</td>
                                    <td>
                                        {
                                            Object.keys(copyObj[date][k].rooms)
                                                .length
                                        }
                                    </td>
                                </tr>
                            ) : null;
                        });
                    })}
                </tbody>
            </table>
        </>
    );
};

export default HotelInfoPreviewArea;
