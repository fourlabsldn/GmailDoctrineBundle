<?php

namespace FL\GmailDoctrineBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use FL\GmailBundle\Services\Directory;
use FL\GmailBundle\Services\OAuth;
use FL\GmailDoctrineBundle\Exception\MissingSyncSettingException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FromType
 * This class needs a list of userIds, and it uses OAuth and Directory to
 * construct a ChoiceType with email addresses.
 */
class FromType extends AbstractType
{
    /**
     * @var string[]
     */
    protected $emailChoices;

    /**
     * @param OAuth                  $oAuth
     * @param Directory              $directory
     * @param EntityManagerInterface $entityManager
     * @param string                 $syncSettingClass
     */
    public function __construct(
        OAuth $oAuth,
        Directory $directory,
        EntityManagerInterface $entityManager,
        string $syncSettingClass
    ) {
        $domain = $oAuth->resolveDomain();
        $syncSetting = $entityManager->getRepository($syncSettingClass)->findOneBy(['domain' => $domain]);

        if (!($syncSetting instanceof SyncSetting)) {
            throw new MissingSyncSettingException('No '.SyncSetting::class.' persisted yet.');
        }

        $emailChoices = [];
        if ($syncSetting->getUserIdsAvailableAsFromAddress() === null) {
            $this->emailChoices = [];

            return;
        }

        foreach ($syncSetting->getUserIdsAvailableAsFromAddress() as $userId) {
            $emailsOfUserId = $directory->resolveEmailsFromUserId($userId, Directory::MODE_RESOLVE_PRIMARY_ONLY);
            if (array_key_exists(0, $emailsOfUserId)) {
                $email = $emailsOfUserId[0];
                $emailChoices[$email] = $email;
            }
        }

        $this->emailChoices = $emailChoices;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', $this->emailChoices);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
