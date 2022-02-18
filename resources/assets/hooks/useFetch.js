import React, { useState, useEffect } from "react";

// fetchを非同期に呼び出し、状態を更新。応答／エラー変数を含むオブジェクトを返す
const useFetch = (url, method, param) => {
    const [response, setResponse] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const res =
                    method.toLowerCase === "post"
                        ? await axios.post(url, param)
                        : await axios.get(url, param);
                setResponse(res?.data?.data);
            } catch (error) {
                setError(error);
            }
        };

        fetchData();
    }, []);

    return { response, error };
};

export default useFetch;
