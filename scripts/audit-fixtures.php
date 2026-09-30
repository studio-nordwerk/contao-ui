<?php

declare(strict_types=1);

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;

require getcwd().'/vendor/autoload.php';

ContaoKernel::setProjectDir(getcwd());
$kernel = new ContaoKernel($_SERVER['APP_ENV'] ?? 'dev', 'prod' !== ($_SERVER['APP_ENV'] ?? 'dev'));
$kernel->boot();
$kernel->getContainer()->get('contao.framework')->initialize();
$db = $kernel->getContainer()->get('database_connection');
$tables = ['tl_content', 'tl_article', 'tl_page', 'tl_module', 'tl_member', 'tl_member_group', 'tl_user', 'tl_user_group'];

if ('remove' === ($argv[1] ?? null)) {
    $records = json_decode($argv[2], true, flags: JSON_THROW_ON_ERROR);

    foreach ($tables as $table) {
        foreach ($records[$table] ?? [] as $id) {
            $db->delete($table, ['id' => (int) $id]);
        }
    }
    exit(0);
}

$records = [];
$save = static function (string $table, array $data) use (&$records): int {
    $class = Model::getClassFromTable($table);
    $model = new $class();
    $model->setRow(['tstamp' => time(), ...$data]);
    $model->save();
    $id = (int) $model->id;
    $records[$table][] = $id;

    return $id;
};
$token = 'audit-'.bin2hex(random_bytes(6));
$payload = 'Audit <img src=x onerror=alert(1)> " & </h2>';
$rootId = (int) $db->fetchOne("SELECT id FROM tl_page WHERE type = 'root' ORDER BY id LIMIT 1");
$themeId = (int) $db->fetchOne('SELECT id FROM tl_theme ORDER BY id LIMIT 1');
$db->beginTransaction();

try {
    $groupId = $save('tl_member_group', ['name' => $token]);
    $save('tl_member', ['firstname' => 'Synthetic', 'lastname' => 'Audit', 'email' => $token.'@example.test', 'login' => 1, 'username' => $token, 'password' => password_hash('contao-ui-audit-only', PASSWORD_BCRYPT), 'groups' => serialize([$groupId])]);
    $pageId = $save('tl_page', ['pid' => $rootId, 'title' => $token, 'alias' => $token, 'type' => 'regular', 'published' => 1, 'hide' => 1, 'includeChmod' => 1, 'chmod' => serialize(['w4']), 'includeCache' => 1, 'cache' => 300, 'clientCache' => 300]);
    $articleId = $save('tl_article', ['pid' => $pageId, 'title' => $token, 'alias' => $token, 'inColumn' => 'main', 'published' => 1]);

    foreach (['nw_carousel' => 'nwCarouselLabel', 'nw_sheet' => 'nwSheetLabel'] as $type => $field) {
        $parentId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'type' => $type, $field => $payload]);
        $save('tl_content', ['pid' => $parentId, 'ptable' => 'tl_content', 'type' => 'text', 'text' => '<p>Public '.$type.'</p>']);
        $save('tl_content', ['pid' => $parentId, 'ptable' => 'tl_content', 'type' => 'text', 'text' => '<p>Private '.$type.' '.$token.'</p>', 'protected' => 1, 'groups' => serialize([$groupId])]);
    }
    $sheetId = $parentId;
    $buttonId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'type' => 'nw_sheet_button', 'nwSheetTarget' => $sheetId, 'nwSheetButtonLabel' => 'Audit open']);
    $editorGroupId = $save('tl_user_group', ['name' => $token, 'alexf' => serialize(['tl_content::nwSheetTarget', 'tl_content::nwSheetButtonLabel', 'tl_content::nwSheetAction'])]);
    $save('tl_user', ['username' => $token.'-editor', 'name' => 'Synthetic editor', 'email' => $token.'-editor@example.test', 'password' => password_hash('contao-ui-audit-only', PASSWORD_BCRYPT), 'language' => 'de', 'inherit' => 'custom', 'groups' => serialize([$editorGroupId]), 'modules' => serialize(['article']), 'pagemounts' => serialize([$pageId]), 'elements' => serialize(['nw_sheet_button', 'nw_sheet', 'nw_carousel', 'text']), 'alpty' => serialize(['regular']), 'cud' => serialize(['tl_content::update'])]);
    $foreignSheetId = (int) $db->fetchOne("SELECT id FROM tl_content WHERE type = 'nw_sheet' AND id <> ? ORDER BY id LIMIT 1", [$sheetId]);
    $loginPageId = $save('tl_page', ['pid' => $rootId, 'title' => $token.' login', 'alias' => $token.'-login', 'type' => 'regular', 'published' => 1, 'hide' => 1]);
    $loginArticleId = $save('tl_article', ['pid' => $loginPageId, 'title' => $token, 'alias' => $token.'-login', 'inColumn' => 'main', 'published' => 1]);
    $moduleId = $save('tl_module', ['pid' => $themeId, 'type' => 'login', 'name' => $token, 'jumpTo' => $pageId]);
    $save('tl_content', ['pid' => $loginArticleId, 'ptable' => 'tl_article', 'type' => 'module', 'module' => $moduleId]);
    $db->commit();
    echo json_encode(['records' => $records, 'token' => $token, 'payload' => $payload, 'articleId' => $articleId, 'buttonId' => $buttonId, 'sheetId' => $sheetId, 'foreignSheetId' => $foreignSheetId], JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    $db->rollBack();

    throw $exception;
}
