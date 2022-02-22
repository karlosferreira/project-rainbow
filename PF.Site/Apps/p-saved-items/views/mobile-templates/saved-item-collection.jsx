<div style={{ width: "50%", padding: 8, flexDirection: "column"}} onPress={$.showDetail}>
    {$.item.id != 9999999999 ? <image source={{uri: $.item.image}} resizeMode={"cover"} style={{ width: "100%", height: 150, borderRadius: 4 }}/> : null}
    {$.item.id != 9999999999 ? <div style={{ marginTop: 8, height: 40, paddingTop: 4, paddingBottom: 8, width: "100%", flexDirection: "row"}}>
        <div style={{ width: $.item.can_action ? "80%" : "100%" }}>
            <a style={{ fontSize: "large", fontWeight: "bold", color: "normal" }} url={`saveditems-collection/${$.item.id}`} numberOfLines={1}>{$.item.name}</a>
            <p style={{ marginTop: 2, fontSize: "xsmall", color: "light" }}>{$.item.total_item == 1 ? $.translate('saveditems_collection_item', {number: $.item.total_item}) : $.translate('saveditems_collection_items', {number: $.item.total_item})}</p>
        </div>
        {$.item.can_action && <div style={{ width: "100%", paddingTop: 4, paddingLeft: 10, color: "light" }} onPress={$.showActionMenu}><icon name="dottedmore-o" color="light"/></div>}
    </div> : null}
</div>