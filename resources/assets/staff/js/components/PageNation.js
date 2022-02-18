import { range } from "lodash";
import React from "react";

const PageNation = props => {
    return (
        <ol id="pageNation">
            <li>
                <a onClick={e => props.onClick(e, 1, props?.currentTab)}>
                    <span className="material-icons">first_page</span>
                </a>
            </li>
            {range(1, props.lastPage + 1).map((page, index) => (
                <li key={index}>
                    {props.currentPage == page ? (
                        <span className="stay">{page}</span>
                    ) : (
                        <a
                            onClick={e =>
                                props.onClick(e, page, props?.currentTab)
                            }
                        >
                            {page}
                        </a>
                    )}
                </li>
            ))}
            <li>
                <a
                    onClick={e =>
                        props.onClick(e, props.lastPage, props?.currentTab)
                    }
                >
                    <span className="material-icons">last_page</span>
                </a>
            </li>
        </ol>
    );
};

export default PageNation;
