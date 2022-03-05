<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 20/6/18
 * Time: 4:29 PM
 */

namespace Apps\Core_MobileApi\Version1_7_3\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;

class ContactUsForm extends GeneralForm
{

    protected $category = [];
    protected $fullName = '';
    protected $email = '';
    protected $isUser = false;

    public function buildForm()
    {
        if (!empty($this->category)) {
            $this
                ->addField('category_id', ChoiceType::class, [
                    'label'       => 'category',
                    'placeholder' => 'select',
                    'options'     => $this->category,
                    'required'    => true
                ]);
        } else {
            $this
                ->addField('category_id', HiddenType::class, [
                    'value' => '#'
                ]);
        }
        $this
            ->addField('full_name', $this->isUser && $this->fullName ? HiddenType::class : TextType::class, [
                'label' => 'full_name',
                'value' => $this->fullName,
                'required' => true
            ])
            ->addField('subject', TextType::class, [
                'label' => 'subject',
                'required' => true
            ])
            ->addField('email', $this->isUser && $this->email ? HiddenType::class : EmailType::class, [
                'label' => 'email',
                'value' => $this->email,
                'required' => true
            ])
            ->addField('text', TextareaType::class, [
                'label' => 'message',
                'required' => true
            ])
            ->addField('copy', CheckboxType::class, [
                'label' => 'send_yourself_a_copy'
            ])
        ;
        $this->addField('submit', SubmitType::class, [
            'label' => 'submit',
            'value' => 1
        ]);
    }

    /**
     * @param array $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param bool $isUser
     */
    public function setIsUser($isUser)
    {
        $this->isUser = $isUser;
    }
}