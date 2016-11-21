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

fl_gmail:
    credentials_storage_service: fl_gmail_doctrine.credentials_storage
    
fl_gmail_doctrine:
  sync_setting_class: TriprHqBundle\Entity\GmailSyncSetting
  credentials_class: TriprHqBundle\Entity\GmailCredentials
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

- Provides implementation of `credentials_storage_service`, required by [GmailBundle](https://github.com/fourlabsldn/GmailBundle). 
    - See more at `FL\GmailDoctrineBundle\Storage\CredentialsStorage`
    - See more at `Resources/config/services/storage.yml`
- A sync command, i.e. `php bin/console fl:gmail_doctrine:sync`
- Event Listeners, that will save what we fetch from Google into the database. See more at the `EventListener` folder.
- `FL\GmailDoctrineBundle\Entity\SyncSetting` entity:
    - Allows you to pick which email inboxes you want to sync, and send email from.
    - See corresponding form, `FL\GmailDoctrineBundle\Form\SyncSettingType`.
- `FL\GmailDoctrineBundle\Model\OutgoingEmail` model class:
    - Represents an Outgoing Email. 
    - See corresponding form, `FL\GmailDoctrineBundle\Form\OutgoingEmailType`.
    - From field, according to what you have enabled through `FL\GmailDoctrineBundle\Entity\SyncSetting`.
- `FL\GmailDoctrineBundle\Services\GoogleClientStatusWrapper` is a wrapper for `FL\GmailBundle\Services\GoogleClientStatus`.
    - Copies the token authentication method, `GoogleClientStatusWrapper::isAuthenticated`.
    - And two more methods `GoogleClientStatusWrapper::isSetupForDomain(string $domain)` and `GoogleClientStatusWrapper::isSetupForAtLeastOneDomain()`

### Limitations
- At the moment, this implementation assumes you are only managing one Google Apps Domain per Symfony application. 

## License

GmailDoctrineBundle is licensed under the MIT license.

