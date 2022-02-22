<div style={{
    padding: 10,
    borderColor: "gray_lighter",
    borderWidth: "small",
    flex: 1,
    flexDirection: "row",
    height: 150
}}>
    <link url={$.item.link} onPress={() => {if (!$.item.in_collection &&$.item.is_unopened == 1) $.customAction('saveditems/open_url')}}>
        <image source={{uri: $.item.image}} style={{width: 130, height: 130}} resizeMode={"cover"}></image>
    </link>
    <div style={{paddingLeft: 8, flex: 1}}>
        <div style={{color: "normal", fontSize: "xlarge", flexDirection: "row"}}>
            {(!$.item.in_collection && $.item.is_unopened == 1)? <div style={{
                position: "absolute",
                top: 7,
                marginRight: 4,
                width: 8,
                height: 8,
                borderRadius: 4,
                borderColor: "gray_lighter",
                borderWidth: "small",
                backgroundColor: "#2681d5"
            }}></div>: <div></div>}
            <a style={{color: "normal"}}
               url={$.item.link}
               params={{ query: { no_message: 1, } }} numberOfLines={2} onPress={() => $.item.is_unopened == 1 && $.customAction('saveditems/open_url')}>{$.item.is_unopened == 1 ? '    ' : ''}{$.item.title}</a>
        </div>
        <div style={{flexDirection: "row", paddingTop: 2}}>
            <p style={{fontSize: "xsmall", marginRight: 4, maxWidth: 140, color: "light"}} numberOfLines={1}>{$.item.item_type_name}</p>
            <div style={{paddingLeft: 8, width: 100}}>
                <p style={{fontSize: "xsmall", position: "absolute", top: -4, left: 0, color: "light"}}>.</p>
                <p numberOfLines={1} style={{fontSize: "xsmall", color: "light"}}>{$.translate('by')} <a style={{
                    fontSize: "xsmall",
                    color: "normal",
                    flexWrap: "wrap"
                }} url={`user/${$.user.id}`}>{$.user.full_name}</a></p>
            </div>
        </div>

        {$.item.additional_information ? <p numberOfLines={2} style={{paddingTop: 2, fontSize: "xsmall", color: "normal"}}>{$.item.additional_information.type == 'price' && <span style={{color: "#f0ad4e"}}>{$.item.additional_information.value}</span>}{$.item.additional_information.type == 'link' && <a href={$.item.additional_information.value}>{$.item.additional_information.title}</a>}{$.item.additional_information.type == 'date_time' || $.item.additional_information.type == 'other' ? $.parseHtml($.item.additional_information.value) : null}</p> : null}
        { $.item.total_collection == 1 &&
            <p numberOfLines={1} style={{ paddingTop: 2 }}>
                <p style={{fontSize: "xsmall", color: "light"}}>{$.translate('saveditems_saved_to_title')} </p>
                <a style={{fontSize: "xsmall"}} params={{ query: { saved_id: $.item.id } }} url={`saveditems-collection/${$.item.default_collection_id}`}>{$.item.default_collection_name}</a>
            </p> }
        { $.item.total_collection > 1 && <div numberOfLines={1} style={{ flexDirection: "row", paddingTop: 2 }}>
            <p style={{fontSize: "xsmall", color: "light"}}>{$.translate('saveditems_saved_to_title')} </p>
            <a numberOfLines={1} url="saveditems-collection/list-item" params={{ headerTitle: $.translate('saveditems_collections'), query: { saved_id: $.item.id } }} style={{ color: "primary", fontSize: "xsmall" }}>{$.translate('saveditems_number_collections', {number: $.item.total_collection})}</a>
        </div> }
    </div>
    <p style={{width: 30, paddingLeft: 8, color: "light"}} onPress={$.showActionMenu}>
        <icon name="dottedmore-o"/>
    </p>
</div>