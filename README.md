# TYPO3 Mail Catcher

Catch emails sent by the mail API of TYPO3 and view them in the backend.

This extension is an alternative for development or staging environments where it is not possible to install other tools like MailHog.

## Install

Composer or TER

## Integration

You must set the mail transport to null:

```PHP
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'null'
```

