<?php

/*
 * Copyright (C) 2002-2013 AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE
 *
 */

/**
 * @property int $IdHelpdeskUser
 * @property int $IdSystemUser
 * @property int $IdTenant
 * @property bool $Activated
 * @property string $ActivateHash
 * @property bool $Blocked
 * @property bool $IsAgent
 * @property string $Name
 * @property string $Email
 * @property string $Language
 * @property string $DateFormat
 * @property int $TimeFormat
 * @property string $PasswordHash
 * @property string $PasswordSalt
 * @property int $Created
 * @property bool $MailNotifications
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class CHelpdeskUser extends api_AContainer
{
	public function __construct()
	{
		parent::__construct(get_class($this));

		$this->SetTrimer(array('Name', 'Email', 'PasswordHash'));

		$this->SetLower(array('Email'));

		$oSettings =& CApi::GetSettings();

		$this->SetDefaults(array(
			'IdHelpdeskUser'		=> 0,
			'IdSystemUser'			=> 0,
			'IdTenant'				=> 0,
			'Activated'				=> false,
			'Blocked'				=> false,
			'IsAgent'				=> false,
			'Name'					=> '',
			'Email'					=> '',
			'ActivateHash'			=> md5(microtime(true).rand(1000, 9999)),
			'Language'				=> $oSettings->GetConf('Common/DefaultLanguage'),
			'DateFormat'			=> $oSettings->GetConf('Common/DefaultDateFormat'),
			'TimeFormat'			=> $oSettings->GetConf('Common/DefaultTimeFormat'),
			'PasswordHash'			=> '',
			'PasswordSalt'			=> md5(microtime(true).rand(10000, 99999)),
			'MailNotifications'		=> false,
			'Created'				=> time()
		));
	}

	public function RegenerateActivateHash()
	{
		$this->ActivateHash = md5(microtime(true).rand(1000, 9999).$this->ActivateHash);
	}

	/**
	 * @param string $sPassword
	 */
	public function SetPassword($sPassword)
	{
		$this->PasswordHash = md5($sPassword.'/'.$this->PasswordSalt);
	}

	/**
	 * @param string $sPassword
	 *
	 * @return bool
	 */
	public function ValidatePassword($sPassword)
	{
		return $this->PasswordHash === md5($sPassword.'/'.$this->PasswordSalt);
	}

	/**
	 * @return bool
	 */
	public function Validate()
	{
		switch (true)
		{
			case api_Validate::IsEmpty($this->Email):
				throw new CApiValidationException(Errs::Validation_FieldIsEmpty, null, array(
					'{{ClassName}}' => 'CHelpdeskUser', '{{ClassField}}' => 'Email'));
				
			case api_Validate::IsEmpty($this->PasswordHash):
				throw new CApiValidationException(Errs::Validation_FieldIsEmpty, null, array(
					'{{ClassName}}' => 'CHelpdeskUser', '{{ClassField}}' => 'PasswordHash'));
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function ActivationLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ').'/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sHash = substr(md5($this->IdTenant.CApi::$sSalt), 0, 8);
			$sPath .= '='.$sHash;
		}

		$sPath .= '&activate='.$this->ActivateHash;

		return $sPath;
	}

	/**
	 * @return string
	 */
	public function ForgotLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ').'/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sHash = substr(md5($this->IdTenant.CApi::$sSalt), 0, 8);
			$sPath .= '='.$sHash;
		}

		$sPath .= '&forgot='.$this->ActivateHash;

		return $sPath;
	}
	
	/**
	 * @return array
	 */
	public function GetMap()
	{
		return self::GetStaticMap();
	}

	/**
	 * @return array
	 */
	public static function GetStaticMap()
	{
		return array(
			'IdHelpdeskUser'	=> array('int', 'id_helpdesk_user', false, false),
			'IdSystemUser'		=> array('int', 'id_system_user', true, false),
			'IdTenant'			=> array('int', 'id_tenant', true, false),
			'IsAgent'			=> array('bool', 'is_agent'),
			'Activated'			=> array('bool', 'activated'),
			'ActivateHash'		=> array('string', 'activate_hash'),
			'Blocked'			=> array('bool', 'blocked'),
			'Email'				=> array('string', 'email', true, false),
			'Name'				=> array('string', 'name'),
			'Language'			=> array('string(100)', 'language'),
			'DateFormat'		=> array('string(50)', 'date_format'),
			'TimeFormat'		=> array('int', 'time_format'),
			'PasswordHash'		=> array('string', 'password_hash'),
			'PasswordSalt'		=> array('string', 'password_salt'),
			'MailNotifications'	=> array('bool', 'mail_notifications'),
			'Created'			=> array('datetime', 'created', true, false)
		);
	}
}