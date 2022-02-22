<upgrade>
	<settings>
		<setting>
			<group />
			<module_id>feed</module_id>
			<is_hidden>0</is_hidden>
			<type>integer</type>
			<var_name>refresh_activity_feed</var_name>
			<phrase_var_name>setting_refresh_activity_feed</phrase_var_name>
			<ordering>1</ordering>
			<version_id>3.0.0beta1</version_id>
			<value>0</value>
		</setting>
	</settings>
	<phpfox_update_settings>
		<setting>
			<group />
			<module_id>feed</module_id>
			<is_hidden>0</is_hidden>
			<type>boolean</type>
			<var_name>allow_rating_of_feeds</var_name>
			<phrase_var_name>setting_allow_rating_of_feeds</phrase_var_name>
			<ordering>1</ordering>
			<version_id>2.0.0rc4</version_id>
			<value>0</value>
		</setting>
		<setting>
			<group />
			<module_id>feed</module_id>
			<is_hidden>0</is_hidden>
			<type>boolean</type>
			<var_name>enable_like_system</var_name>
			<phrase_var_name>setting_enable_like_system</phrase_var_name>
			<ordering>1</ordering>
			<version_id>2.0.0rc12</version_id>
			<value>1</value>
		</setting>
	</phpfox_update_settings>
	<blocks>
		<block>
			<type_id>0</type_id>
			<m_connection>group.view</m_connection>
			<module_id>feed</module_id>
			<component>display</component>
			<location>2</location>
			<is_active>1</is_active>
			<ordering>12</ordering>
			<disallow_access />
			<can_move>0</can_move>
			<title>Group Feeds</title>
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>event.view</m_connection>
			<module_id>feed</module_id>
			<component>display</component>
			<location>4</location>
			<is_active>1</is_active>
			<ordering>7</ordering>
			<disallow_access />
			<can_move>0</can_move>
			<title>Activity Feed</title>
			<source_code />
			<source_parsed />
		</block>
	</blocks>
	<phpfox_update_blocks>
		<block>
			<type_id>0</type_id>
			<m_connection>core.index-member</m_connection>
			<module_id>feed</module_id>
			<component>display</component>
			<location>2</location>
			<is_active>1</is_active>
			<ordering>12</ordering>
			<disallow_access />
			<can_move>0</can_move>
			<title>Activity Feed</title>
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>profile.index</m_connection>
			<module_id>feed</module_id>
			<component>display</component>
			<location>2</location>
			<is_active>1</is_active>
			<ordering>4</ordering>
			<disallow_access />
			<can_move>1</can_move>
			<title />
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>core.index-visitor</m_connection>
			<module_id>feed</module_id>
			<component>display</component>
			<location>2</location>
			<is_active>0</is_active>
			<ordering>7</ordering>
			<disallow_access />
			<can_move>0</can_move>
			<title>Activity Feed</title>
			<source_code />
			<source_parsed />
		</block>
	</phpfox_update_blocks>
	<sql><![CDATA[a:3:{s:9:"ADD_FIELD";a:1:{s:11:"phpfox_feed";a:4:{s:6:"app_id";a:4:{i:0;s:7:"UINT:10";i:1;s:1:"0";i:2;s:0:"";i:3;s:2:"NO";}s:7:"privacy";a:4:{i:0;s:6:"TINT:1";i:1;s:1:"0";i:2;s:0:"";i:3;s:2:"NO";}s:15:"privacy_comment";a:4:{i:0;s:6:"TINT:1";i:1;s:1:"0";i:2;s:0:"";i:3;s:2:"NO";}s:14:"parent_user_id";a:4:{i:0;s:7:"UINT:10";i:1;s:1:"0";i:2;s:0:"";i:3;s:2:"NO";}}}s:7:"ADD_KEY";a:1:{s:11:"phpfox_feed";a:4:{s:9:"privacy_2";a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"privacy";i:1;s:10:"time_stamp";}}s:7:"privacy";a:2:{i:0;s:5:"INDEX";i:1;a:3:{i:0;s:7:"privacy";i:1;s:7:"user_id";i:2;s:10:"time_stamp";}}s:9:"privacy_3";a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"privacy";i:1;s:7:"user_id";}}s:9:"privacy_4";a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"privacy";i:1;s:14:"parent_user_id";}}}}s:10:"REMOVE_KEY";a:1:{s:11:"phpfox_feed";a:7:{i:0;a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"type_id";i:1;s:12:"item_user_id";}}i:1;a:2:{i:0;s:5:"INDEX";i:1;s:7:"user_id";}i:2;a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"type_id";i:1;s:7:"user_id";}}i:3;a:2:{i:0;s:5:"INDEX";i:1;s:7:"type_id";}i:4;a:2:{i:0;s:5:"INDEX";i:1;s:7:"is_like";}i:5;a:2:{i:0;s:5:"INDEX";i:1;a:3:{i:0;s:7:"view_id";i:1;s:7:"type_id";i:2;s:12:"item_user_id";}}i:6;a:2:{i:0;s:5:"INDEX";i:1;a:2:{i:0;s:7:"view_id";i:1;s:7:"user_id";}}}}}]]></sql>
</upgrade>