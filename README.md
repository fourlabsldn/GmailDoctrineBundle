# FL\GmailDoctrineBundle

GmailDoctrineBundle provides you a Doctrine implementation of [GmailBundle](https://github.com/fourlabsldn/GmailBundle). 

[![StyleCI](https://styleci.io/repos/70260536/shield?branch=master)](https://styleci.io/repos/70260536)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3ed528cf-1d9b-4241-b91a-90eba601f5d4/mini.png)](https://insight.sensiolabs.com/projects/3ed528cf-1d9b-4241-b91a-90eba601f5d4)

## Installation

```bash
    $ composer require fourlabs/gmail-doctrine-bundle
```

## Configuration

```
// app/config/config.yml
    
fl_gmail_doctrine:
  sync_setting_class: TriprHqBundle\Entity\GmailSyncSetting
```

## Setup

- Create doctrine entities in your entities folder e.g. `AppBundle\Entity`.
- These entities must extend all the MappedSuperClasses in this bundle's `Entity` folder.
- Make sure you use the provided repositories (from the entity folder). Or extend the repositories.

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FL\GmailDoctrineBundle\Entity\SyncSetting;

/**
 * @ORM\Entity(repositoryClass="FL\GmailDoctrineBundle\Entity\SyncSettingRepository")
 */
class GmailSyncSetting extends SyncSetting
{
}

```

### Why GmailDoctrineBundle?

- A sync command that lets you sync gmailIds, gmailMessages, or both. i.e.  with two options: 
    - Example `php bin/console fl:gmail_doctrine:sync --mode=both --limit_messages_per_user=100`.
    - Required Option `mode`: Can be `gmail_ids`, `gmail_messages`, or `both`.
    - Option `limit_messages_per_user`: Required for `mode=gmail_ids` or `mode=both`. Must be a positive integer.
    - Suggestion: a limit of 300 message per user, should prevent you from hitting google throttling.
- Event Listeners, that will save what we fetch from Google into the database. See more at the `EventListener` folder.
- `FL\GmailDoctrineBundle\Entity\SyncSetting` entity:
    - Allows you to pick which email inboxes you want to sync, and send email from.
    - See corresponding form, `FL\GmailDoctrineBundle\Form\Type\SyncSettingType`.
- `FL\GmailDoctrineBundle\Model\OutgoingEmail` model class:
    - Represents an Outgoing Email. 
    - See corresponding form, `FL\GmailDoctrineBundle\Form\Type\OutgoingEmailType`.
    - From field, according to what you have enabled through `FL\GmailDoctrineBundle\Entity\SyncSetting`.
- `FL\GmailDoctrineBundle\Services\GoogleClientStatusWrapper` is a wrapper for `FL\GmailBundle\Services\GoogleClientStatus`.
    - Copies the authentication method, `GoogleClientStatusWrapper::isAuthenticated`.
    - And two more methods `GoogleClientStatusWrapper::isSetupForDomain(string $domain)` and `GoogleClientStatusWrapper::isSetupForAtLeastOneDomain()`

## License

GmailDoctrineBundle is licensed under the MIT license.

