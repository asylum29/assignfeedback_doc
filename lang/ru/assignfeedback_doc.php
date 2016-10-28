<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * doc feedback plugin
 *
 * @package    assignfeedback_doc
 * @copyright  2016 Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Отзыв в виде документа';
$string['enabled'] = 'Отзыв в виде документа';
$string['enabled_help'] = 'Если включен, учитель сможет разместить отзыв-документ на каждый ответ.';
$string['default'] = 'Включено по умолчанию';
$string['default_help'] = 'При включенном параметре способ отзыва будет включен по умолчанию для всех новых заданий.';
$string['script'] = 'Путь к JavaScript';
$string['script_help'] = 'Позволяет внедрить заданный JavaScript в каждый отзыв-документ.';
$string['coursename'] = 'Название курса для отзыва';
$string['coursename_help'] = 'Данное название будет использоваться в автоматически формируемых отзывах-документах.';
$string['key1'] = 'ОТЗЫВ НА ЗАДАНИЕ «{$a->assignname}»<br />ПО ДИСЦИПЛИНЕ «{$a->coursename}»';
$string['key2'] = 'Ф.И.О. обучающегося';
$string['key3'] = 'Группа';
$string['key4'] = 'Преподаватель';
$string['key5'] = 'Оценка';
$string['key6'] = 'Отзыв';
