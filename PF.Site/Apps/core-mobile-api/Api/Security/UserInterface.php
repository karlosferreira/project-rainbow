<?php

namespace Apps\Core_MobileApi\Api\Security;

interface UserInterface
{

    /**
     * Get User's email
     * @return string
     */
    function getEmail();

    /**
     * Get user Id
     * @return int
     */
    function getId();

    /**
     * Get User Group ID
     * @return int
     */
    function getGroupId();

    /**
     * Get UserName string
     * @return string
     */
    function getUserName();

    /**
     * @param UserInterface $user
     *
     * @return bool True if the same user Id
     */
    function compareWith($user);

    /**
     * Get user id without check profile_page
     * @return mixed
     */
    function getRawId();
}