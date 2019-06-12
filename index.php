<?Php
require 'vendor/autoload.php';
try {
    if(file_exists('pifu_path.php'))
        $pifu_path = require 'pifu_path.php';
    else
        $pifu_path = '';
    $parser=new pifu_parser($pifu_path);
}
catch (Exception $e)
{
    die($e->getMessage());
}

$browser = new PifuBrowser();

$schools = $parser->schools();


function description($object)
{
    if(!empty($object->description->long))
        return $object->description->long;
    else
        return $object->description->short;
}

if(!empty($_GET['person']))
{
    $xpath = sprintf('/enterprise/membership/member/sourcedid/id[.="%s"]/ancestor::membership/sourcedid/id', $_GET['person']);//var_dump($xpath);
    $xpath = sprintf('/enterprise/membership/member/sourcedid/id[.="%s"]/ancestor::member', $_GET['person']);//var_dump($xpath);
    $memberships = array();
    foreach ($parser->xml->xpath($xpath) as $membership)
    {
        $group_id = $membership->xpath('parent::membership/sourcedid/id')[0];
        $memberships[] = array(
            'group'=>$parser->group_info_id((string)$group_id),
            'membership'=>$membership);
    }
    echo $browser->render('memberships.twig', array(
        'title'=>'Person',
        'memberships'=>$memberships,
        'person'=>$parser->person($_GET['person']),
        'printr'=>print_r($memberships, true)));
}
elseif(empty($_GET['school']) && empty($_GET['group']))
    echo $browser->render('select_school.twig', array('title'=>'Select school', 'schools'=>$schools));
elseif (empty($_GET['group']))
{
    $group_levels = array(1,2,3,4,7);
    $level_groups = array();
    foreach ($group_levels as $group_level)
    {
        $groups = $parser->ordered_groups($_GET['school'], $group_level);
        $level_groups[$group_level] = array(
            'groups'=>$groups,
            'typevalue'=>(string)array_values($groups)[0]->grouptype->typevalue);
    }

    echo $browser->render('select_group.twig', array(
        'title'=>'Select group',
        'levels'=>$level_groups,
        'unit'=>$parser->group_info_id($_GET['school'])));
}
else
{
    $members = array();
    foreach ($parser->group_members($_GET['group']) as $key=>$member)
    {
        $roletype = (string)$member->role->attributes()['roletype'];
        if(!empty($_GET['roletype']) && $roletype!=$_GET['roletype'])
            continue;
        $members[$key] = array(
            'membership'=>$member,
            'person'=>$parser->person($member->sourcedid->id));
    }

    $group_info = $parser->group_info_id($_GET['group']);

    echo $browser->render('group_members.twig', array(
        'title'=>sprintf('Members of %s', description($group_info)),
        'members'=>$members,
        'count'=>count($members),
        'printr'=>print_r($members, true),
        'group_info'=>$group_info));
}
