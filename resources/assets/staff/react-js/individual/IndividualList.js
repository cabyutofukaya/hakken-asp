import React, { useEffect, useState } from "react";
import { Link, Redirect } from "react-router-dom";

const IndividualList = ({ agencyAccount }) => {
    // const { errorMessage, setErrorMessage } = useState(null);

    const [individuals, setIndividuals] = useState([]);

    useEffect(() => {
        axios
            .get(`/api/${agencyAccount}/individuals`)
            .then(res => setIndividuals([...res.data.data]))
            .catch(error => {
                if (error?.response) {
                    if (error?.response?.status === 403) {
                        alert("権限エラーです");
                    } else if (error?.response?.status === 401) {
                        alert(
                            "認証エラーです。再度ログインページからアクセス願いします"
                        );

                        location.href = `/${agencyAccount}/login`;
                    }
                }
            });
    }, []);

    const handleTest = e => {
        axios
            .get(`/api/${agencyAccount}/individuals`)
            .then(res => console.log(res.data.data))
            .catch(error => {
                if (error?.response) {
                    if (error?.response?.status === 403) {
                        alert("権限エラーです");
                    } else if (error?.response?.status === 401) {
                        alert(
                            "認証エラーです。再度ログインページからアクセス願いします"
                        );
                        location.href = `/${agencyAccount}/login`;
                    }
                }
                console.log(error);
            });
    };

    return (
        <div>
            <h1>リスト</h1>
            {individuals &&
                individuals.map(row => (
                    <div key={row.id}>
                        <a href={`individual/${row.user_number}`}>
                            {row.last_name}
                        </a>
                    </div>
                ))}
            <button onClick={handleTest}>テストボタン</button>
        </div>
    );
};

export default IndividualList;
