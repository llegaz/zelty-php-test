<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\DevTools;

/**
 * Add  some  data to  (sqlite) DB.
 *
 * Yes this script is ugly but it get the job done. Please disregard it...
 * This application is still under development and this script is for dev purpose only.
 *
 * Also, note that you can use  <b>$usersData</b> array in order to add some more
 * users credentials manually.
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
$container = require __DIR__ . '/bootstrapper.php';

use DateTime;
use DI\Container;
use Doctrine\ORM\EntityManager;
use LLegaz\ZeltyPhpTest\Entities\Article;
use LLegaz\ZeltyPhpTest\Entities\State;
use LLegaz\ZeltyPhpTest\Entities\User;
use LLegaz\ZeltyPhpTest\Helpers\StringValidator as SV;
use LLegaz\ZeltyPhpTest\Utils as ZU;
use LogicException;
use Throwable;
use UnexpectedValueException;
use function bin2hex;
use function microtime;
use function substr;

$usersData = [
    ['login' => 'juju', 'dn' => 'Julie de Zelty', 'passwd' => 'soleilLune456'],
    ['login' => 'titi86', 'dn' => 'Nefertiti', 'passwd' => 'insecure1234'],
    ['login' => 'user1337', 'dn' => 'Nopaleon buenapuerte', 'passwd' => '##R0X0|2P4SSW0rD!:PwithL0t0fCH4r5'],
    ['login' => 'user1234', 'dn' => 'Manu M.', 'passwd' => 'briGitteJtm<3'],
    ['login' => 'ChosenLogin', 'dn' => 'Chosen Display Name', 'passwd' => '4nDCH0senP4ssWD'],
    ['login' => '!nvâlid L0gin', 'dn' => '....', 'passwd' => 'slwkdfjvn2345'],
    ['login' => 'tHis_0ne@is.ok', 'dn' => 'is.ok\'', 'passwd' => 'slwkdfjvn2345'],
    ['login' => 'valid login23', 'dn' => '<--&forbidden characters-->', 'passwd' => '1114lmdskjf1'],
    ['login' => 'thierry', 'dn' => 'thierry', 'passwd' => 'thierry1234'],
    ['login' => 'test@exampel.org', 'dn' => 'test email', 'passwd' => 'HuN:P%PPBcvmZEqi93i'],
    ['login' => 'admin', 'dn' => 'test admin', 'passwd' => 'HuN#:P09FKbcVmZEqi75jj'],
];

$articlesData = [
    ['title' => 'L\'importance du click and collect dans la restauration aujourd\'hui', 'state' => State::PUB, 'date' => '2020-05-19T16:58:35+00:00', 'author' => 'Julie de Zelty'],
    ['title' => 'Les 5 conseils pour choisir et lancer son nouveau concept de restaurant', 'state' => State::PUB, 'date' => '2022-05-25T16:58:35+00:00', 'author' => 'Julie de Zelty'],
    ['title' => 'Les restaurants', 'state' => State::PUB, 'date' => '21-06-18', 'author' => 'Chosen Display Name'],
    ['title' => 'Un super titre plein d\'imagination', 'state' => State::DEL, 'date' => 'now'],
    ['title' => null, 'state' => null, 'date' => null],
    ['title' => null, 'state' => null, 'date' => null],
    ['title' => null, 'state' => null, 'date' => null],
    ['title' => null, 'state' => null, 'date' => null],
    ['title' => null, 'state' => null, 'date' => null],
];

const CONTENT = <<<'EOF'
Beaucoup de choses intéressantes ici...
Ceci est une petite application PHP. Une petite application web
qui permet de gérer des articles à partir d’une API. Il faut s’imaginer que
c’est le début d’une grosse application qui sera amenée à évoluer. Chaque
élément doit être pensé pour pouvoir évoluer, être maintenu facilement et
être performant.
 /!\/!\/!\  /!\/!\/!\  /!\/!\/!\
La partie front n’est pas requise. L’évaluation portera uniquement sur le côté back/api.
 /!\/!\/!\  /!\/!\/!\  /!\/!\/!\
SPECIMEN
Ceci est un texte d'exemple afin de réaliser des tests au niveau API.
EOF;

/**
 * @throws LogicException
 * @throws InvalidArgumentException
 */
function validateUserData(array $data): void
{
    if (!isset($data['passwd']) || !isset($data['login'])) {
        throw new LogicException('invalid user data');
    }
    $data['dn'] ??= 'anonymous';
    SV::validatePasswordString(null, $data['passwd'], 'Password: "' . $data['passwd'] . '" =>');
    SV::validateConstrainedUnescapedString(null, $data['dn'], 'Display Name: "' . $data['dn'] . '" =>');
    SV::validateLoginString(null, $data['login'], 'User login: "' . $data['login'] . '" =>');
}

/**
 * @throws LogicException
 * @throws InvalidArgumentException
 */
function createUser(
    Container $c,
    EntityManager &$em,
    string $pwd_salt,
    array $data
): User {
    validateUserData($data);
    $test = $em->getRepository(User::class)->findOneBy(['login' => $data['login']]);

    if (null !== $test) {
        throw new UnexpectedValueException('user already exists with login ' . $data['login']);
    }
    $usr = new User();
    ZU::colorYellowToCLI('--  creating new User for Realm: ' . $c->get('digest-scheme')['realm'] . ' with login="' . $data['login'] . '"  --');
    $pwd_hash = $c->get('auth')->getA1Hash($data['login'], $data['passwd']);
    ZU::colorBlueToCLI($pwd_hash);
    $usr->generateUserId()
        ->setDisplayName($data['dn'])
        ->setLogin($data['login'])
        ->setPasswordHash($pwd_hash)
        ->setPasswordSalt(bin2hex($pwd_salt))
    ;
    $em->persist($usr);

    return $usr;
}

ZU::colorGreenToCLI('Now importing some data fixtures into project DB');

try {
    $salt = $container->get('auth')->retrieveOpaque(true);
    $em   = $container->get(EntityManager::class);
    $i    = $usersAdded    = 0;
    /**
     * some DEBUG utilities.
     */
    $dumper = $container->get('debug')['dumper'];
    $cloner = $container->get('debug')['cloner'];
    $debug  = false;

    ZU::colorGreenToCLI('importing users in DB');

    foreach ($usersData as $userData) {
        try {
            createUser($container, $em, $salt, $userData);
            ++$usersAdded;
        } catch (UnexpectedValueException $uve) {
            ZU::colorYellowToCLI('Warning: ' . $uve->getMessage());
        } catch (Throwable $t) {
            ZU::colorRedToCLI($t->getMessage());
        }
    }
    $randomId = substr(microtime(), 2, 6);
    $default  = [
        'login'  => 'default_user_' . $randomId,
        'dn'     => 'default name' . $randomId,
        'passwd' => 'default_password',
    ];
    $u = createUser($container, $em, $salt, $default);
    $em->flush();
    ++$usersAdded;

    ZU::colorGreenToCLI('importing articless in DB');

    foreach ($articlesData as $articleData) {
        if (isset($articleData['author'])) {
            $author = $em->getRepository(User::class)->findOneBy(['displayName' => $articleData['author']]);

            if ($debug && $author) {
                ZU::colorYellowToCLI('new AUTHOR found !!! ');
                $dumper->dump($cloner->cloneVar($author));
            }
        }
        $a = new Article();
        $a->generateArticleId()
            ->setAuthor($author ?? $u)
            ->setTitle($articleData['title'] ?? 'titre article ' . $i)
            ->setContent(CONTENT)
        ;

        if (isset($articleData['state'])) {
            $a->setState($articleData['state'] ?? State::DRAFT)
                ->setPublishedOn(new DateTime($articleData['date'] ?? 'now'))
            ;
        }

        $em->persist($a);
        unset($a, $author);
        ++$i;
    }
    $em->flush();
    $articlesAdded = $i;

    if ($debug) {
        ZU::colorGreenToCLI('final check: ');
        $articleRepository = $em->getRepository(Article::class);
        $articles          = $articleRepository->findAll();

        foreach ($articles as $article) {
            $dumper->dump($cloner->cloneVar($article->getTitle()));
            $dumper->dump($cloner->cloneVar($article->getPublishedOn()));
            $dumper->dump($cloner->cloneVar($article->getAuthor()->getDisplayName()));
        }
    }
    ZU::colorGreenToCLI('Great success !' . PHP_EOL . ' User(s) added = ' . $usersAdded . PHP_EOL . ' Articles added = ' . $articlesAdded);
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
