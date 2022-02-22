<div style={{ marginLeft: 8, marginRight: 8, width: 240, borderColor: "gray_lighter", borderWidth: "small", borderRadius: 4}} onPress={$.showDetail}>
   <image source={{uri: $.item.image}} resizeMode={"cover"} style={{ width: "100%", height: 180, borderRadius: 4 }}>
        <div style={{ position: "absolute", paddingTop: 4, paddingBottom: 8, paddingLeft: 8, paddingRight: 8, backgroundColor: "rgba(0,0,0,0.2)", bottom: 0, zIndex: 10, width: "100%"}}>
            <a style={{ fontSize: "large", color: "rgba(255, 255, 255, 1)", fontWeight: "bold" }} url={`saveditems-collection/${$.item.id}`} numberOfLines={1}>{$.item.name}</a>
            <p style={{ marginTop: 2, fontSize: "xsmall", color: "rgba(255, 255, 255, 1)" }}>{$.item.total_item == 1 ? $.translate('saveditems_collection_item', {number: $.item.total_item}) : $.translate('saveditems_collection_items', {number: $.item.total_item})}</p>
        </div>
    </image>
</div>