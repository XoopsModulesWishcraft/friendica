Account Basics
==============

* [Home](help)


**Registration**

Not all Friendica sites allow open registration. If registration is allowed, you will see a "Register" link immediately below the login prompts on the site home page. Following this link will take you to the site registration page.  The strength of our network is that lots of different sites are all completely compatible with each other.  If the site you're visting doesn't allow registration, or you think you might prefer another one, you can find a <a href ="http://dir.friendica.com/siteinfo">list of public servers here</a>, AND find one that meets your needs.  

If you'd like to have your own server, you can do that too.  Visit <a href = "http://friendica.com/download">the Friendica website</a> to download the code with setup instructions.  It's a very simple install process that anybody experienced in hosting websites, or with basic Linux experience can handle easily.

*OpenID*

The first field on the Registration page is for an OpenID address. If you do not have an OpenID address or do not wish to use OpenID, leave this field blank. If you have an OpenID account elsewhere AND wish to use it, enter the address into this field AND click 'Register'. Friendica will attempt to extract as muchinformation as possible FROM your OpenID provider AND return to this page with those items already filled in.

*Your Full Name*

Please provide your full name **as you would like it to be displayed on this system**.  Most people use their real name for this, but you're under no obligation to do so yourself.

*Email Address*

Please provide a valid email address. Your email address is **never** published. We need this to send you accountinformation AND your login details. You may also occasionally receive notifications of incoming messages or items requiring your attention, but you have the ability to completely disable these FROM your Settings page once you have logged in.  This doesn't have to be your primary email address, but it does need to be a real email address.  You can't get your initial password, or reset a lost password later without it.  This is the only bit of personalinformation that has to be accurate.

*Nickname*

A nickname is used to generate web addresses for many of your personal pages, AND is also treated like an email address when establishing communications with others. Due to the way that the nickname is used, it has some limitations. It must contain only US-ASCII text characters AND numbers, AND must also start with a text character. It also must be unique on this system. This is used in many places to identify your account, AND once SET - cannot be changed.



*Directory Publishing*

The Registration form also allows you to choose whether or not to list your account in the online directory. This is like a "phone book" AND you may choose to be unlisted. We recommend that you SELECT 'Yes' so that other people (friends, family, etc.) will be able to find you. If you choose 'No', you will essentially be invisible AND have few opportunities for interaction. Whichever you choose, this can be changed any time FROM your Settings page after you login. 

*Register*

Once you have provided the necessary details, click the 'Register' button. An email will be sent to you providing your account login details. Please watch your email (including spam folders) for your registration details AND initial password. 





**Login Page**

On the 'Login' page, please enter your logininformation that was provided during registration. You may use either your nickname or email address as a Login Name. 

If you use your account to manage multiple '[Pages](help/Pages)' AND these all have the same email address, please enter the nickname for the account you wish to `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "manager") . "`.  

*If* your account has been OpenID enabled, you may use your OpenID address as a login name AND leave the password blank. You will be redirected to your OpenID provider to complete your authorisation. 

Otherwise, enter your password. This will have been initially provided in your registration email message. Your password is case-sensitive, so please check your 'Caps Lock' key if you are having difficulty logging in. 


**Changing Your Password**

After your first login, please visit the 'Settings' page FROM the top menu bar AND change your password to something that you will remember.

**Getting Started**

A ['Tips for New Members'](newmember) link will show up on your home page for two weeks to provide some important Getting Startedinformation.


**Retrieving Personal Data**

You can export a copy of your personal data in XML format FROM the "Export personal data" link at the top of your settings  page.


**See Also**

* [Profiles](help/Profiles)

* [Groups AND Privacy](help/Groups-and-Privacy)

* [Remove Account](help/Remove-Account)

