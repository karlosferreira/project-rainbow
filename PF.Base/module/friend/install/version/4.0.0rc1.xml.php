<upgrade>
	<phpfox_update_settings>
		<setting>
			<group />
			<module_id>friend</module_id>
			<is_hidden>0</is_hidden>
			<type>integer</type>
			<var_name>birthdays_cache_time_out</var_name>
			<phrase_var_name>setting_birthdays_cache_time_out</phrase_var_name>
			<ordering>1</ordering>
			<version_id>2.0.0beta4</version_id>
			<value>5</value>
		</setting>
		<setting>
			<group />
			<module_id>friend</module_id>
			<is_hidden>0</is_hidden>
			<type>integer</type>
			<var_name>cache_mutual_friends</var_name>
			<phrase_var_name>setting_cache_mutual_friends</phrase_var_name>
			<ordering>2</ordering>
			<version_id>3.6.0rc1</version_id>
			<value>0</value>
		</setting>
		<setting>
			<group />
			<module_id>friend</module_id>
			<is_hidden>0</is_hidden>
			<type>integer</type>
			<var_name>cache_rand_list_of_friends</var_name>
			<phrase_var_name>setting_cache_rand_list_of_friends</phrase_var_name>
			<ordering>3</ordering>
			<version_id>3.6.0rc1</version_id>
			<value>60</value>
		</setting>
		<setting>
			<group />
			<module_id>friend</module_id>
			<is_hidden>1</is_hidden>
			<type>large_string</type>
			<var_name>friend_meta_keywords</var_name>
			<phrase_var_name>setting_friend_meta_keywords</phrase_var_name>
			<ordering>7</ordering>
			<version_id>2.0.0rc1</version_id>
			<value>friends, buddies</value>
		</setting>
	</phpfox_update_settings>
	<phpfox_update_blocks>
		<block>
			<type_id>0</type_id>
			<m_connection>core.index-member</m_connection>
			<module_id>friend</module_id>
			<component>birthday</component>
			<location>3</location>
			<is_active>1</is_active>
			<ordering>4</ordering>
			<disallow_access />
			<can_move>1</can_move>
			<title><![CDATA[{_p var=&#039;upcoming_events&#039;}]]></title>
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>profile.index</m_connection>
			<module_id>friend</module_id>
			<component>mutual-friend</component>
			<location>1</location>
			<is_active>1</is_active>
			<ordering>3</ordering>
			<disallow_access />
			<can_move>1</can_move>
			<title><![CDATA[{_p var=&#039;mutual_friends&#039;}]]></title>
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>core.index-member</m_connection>
			<module_id>friend</module_id>
			<component>suggestion</component>
			<location>3</location>
			<is_active>1</is_active>
			<ordering>5</ordering>
			<disallow_access />
			<can_move>1</can_move>
			<title><![CDATA[{_p var=&#039;suggestions&#039;}]]></title>
			<source_code />
			<source_parsed />
		</block>
		<block>
			<type_id>0</type_id>
			<m_connection>profile.index</m_connection>
			<module_id>friend</module_id>
			<component>remove</component>
			<location>1</location>
			<is_active>1</is_active>
			<ordering>6</ordering>
			<disallow_access />
			<can_move>0</can_move>
			<title>Remove Friend</title>
			<source_code />
			<source_parsed />
		</block>
	</phpfox_update_blocks>
</upgrade>