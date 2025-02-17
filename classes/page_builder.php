<?php

namespace Report\AverageCheckTime;

class PageBuilder 
{
    const _FORM = '_form';
    const PERIOD_FORM = 'period_form';

    const TEACHER_ID = 'teacher_id_';
    const COURSE_ID = 'course_id_';

    private $sortType;
    private $fromDate;
    private $toDate;
    private $teachers;

    function __construct(string $sortType, string $fromDate, string $toDate, $teachers)
    {
        $this->sortType = $sortType;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->teachers = $teachers;
    }

    public function get_page() : string
    {
        $page = $this->get_page_header();
        $page.= $this->get_warning();
        $page.= $this->get_period_box();
        $page.= $this->get_teachers_table();
        $page.= $this->get_forms();

        return $page;
    }

    private function get_page_header() : string 
    {
        $attr = array('class' => 'reportHeader');
        $text = get_string('pluginname', 'report_averagechecktime');
        return \html_writer::tag('h2', $text, $attr);
    }

    private function get_warning() : string 
    {
        $str = $this->get_warning_toggle();
        $str.= $this->get_warning_box();

        $attr = array('class' => 'warningArea');
        $str = \html_writer::tag('div', $str, $attr);

        return $str;
    }

    private function get_warning_toggle() : string 
    {
        $attr = array(
            'id' => 'warningToggle',
            'onclick' => 'toggle_warning()'
        );
        $text = get_string('warning_toggle', 'report_averagechecktime');

        
        return \html_writer::tag('p', $text, $attr);
    }

    private function get_warning_box() : string 
    {
        $text = get_string('known_distortions', 'report_averagechecktime');
        $str = \html_writer::tag('p', $text);

        $text = get_string('distortion_1', 'report_averagechecktime');
        $li = \html_writer::tag('li', $text);
        $text = get_string('distortion_2', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);
        $text = get_string('distortion_3', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);
        $text = get_string('distortion_4', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);
        $text = get_string('distortion_5', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);
        $text = get_string('distortion_6', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);
        $text = get_string('distortion_7', 'report_averagechecktime');
        $li.= \html_writer::tag('li', $text);

        $str.= \html_writer::tag('ol', $li);

        $attr = array(
            'id' => 'warningBox',
            'class' => 'hidden'
        );
        return \html_writer::tag('div', $str, $attr);
    }

    private function get_period_box() : string 
    {
        $text = get_string('period_from', 'report_averagechecktime').' ';
        $text.= $this->get_from_date().' ';
        $text.= get_string('to', 'report_averagechecktime').' ';
        $text.= $this->get_to_date();
        $text.= $this->get_apply_button();

        $str = \html_writer::tag('p', $text);

        return $str;
    }

    private function get_from_date() : string 
    {
        $attr = array(
            'id' => Main::FROM_DATE,
            'type' => 'date',
            'name' => Main::FROM_DATE,
            'value' => $this->fromDate
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_to_date() : string 
    {
        $attr = array(
            'id' => Main::TO_DATE,
            'type' => 'date',
            'name' => Main::TO_DATE,
            'value' => $this->toDate
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_apply_button() : string 
    {
        $attr = array(
            'id' => 'apply_button',
            'type' => 'submit',
            'name' => 'submit_btn',
            'value' => get_string('apply', 'report_averagechecktime'),
            'onclick' => 'add_periods_and_submit_form(`'.self::PERIOD_FORM.'`)'
        );
        $str = \html_writer::empty_tag('input', $attr);

        return $str;
    }

    private function get_teachers_table() : string 
    {
        $attr = array('class' => 'averageTable');
        $str = \html_writer::start_tag('table', $attr);
        $str.= $this->get_teacher_table_header();
        $str.= $this->get_teacher_table_body();
        $str.= \html_writer::end_tag('table');

        return $str;
    }

    private function get_teacher_table_header() : string 
    {
        $sortBy = Main::SORT_BY_NAME;
        $text = get_string('teacher', 'report_averagechecktime');
        $str = $this->get_column_header($sortBy, $text);

        $attr = array('style' => 'cursor:default');
        $text = get_string('module', 'report_averagechecktime');
        $str.= \html_writer::tag('td', $text, $attr);

        $sortBy = Main::SORT_BY_GRADE;
        $text = get_string('average_grade', 'report_averagechecktime');
        $str.= $this->get_column_header($sortBy, $text);

        $sortBy = Main::SORT_BY_TIME;
        $text = get_string('average_check_time', 'report_averagechecktime');
        $str.= $this->get_column_header($sortBy, $text);

        $str = \html_writer::tag('tr', $str);
        $str = \html_writer::tag('thead', $str);

        return $str; 
    }

    private function get_column_header(string $sortBy, string $text) : string 
    {
        $attr = array(
            'onclick' => 'add_periods_and_submit_form(`'.$sortBy.self::_FORM.'`)',
            'title' => get_string('sort_title', 'report_averagechecktime')
        );

        $str = $text;
        if($this->sortType == $sortBy)
        {
            $str.= '↓';
        }

        return \html_writer::tag('td', $str, $attr);
    }

    private function get_teacher_table_body() : string 
    {
        $str = \html_writer::start_tag('tbody');

        $teacherNumber = 1;
        foreach($this->teachers as $teacher)
        {
            $str.= $this->get_teacher_row($teacher, $teacherNumber);
            $teacherNumber++;
        }

        $str.= \html_writer::end_tag('tbody');

        return $str;
    }

    private function get_teacher_row(\stdClass $teacher, int $teacherNumber) : string 
    {
        $attr = array(
            'id' => self::TEACHER_ID.$teacherNumber,
            'class' => 'teacher-row',
            'onclick' => 'toggle_courses_rows_visibility(this)',
            'title' => get_string('more_info_title', 'report_averagechecktime')
        );
        $str = \html_writer::start_tag('tr', $attr);

        $text = $teacherNumber.'. '.$teacher->name;
        $str.= \html_writer::tag('td', $text);

        // Module cell
        $str.= \html_writer::tag('td', '');

        $attr = array('class' => 'tac');
        $text = round($teacher->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $teacher->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        $str.= $this->get_courses_rows($teacher, $teacherNumber);

        return $str;
    }

    private function get_courses_rows(\stdClass $teacher, int $teacherNumber) : string 
    {
        $str = '';

        $courseNumber = 1;
        foreach($teacher->courses as $course)
        {
            $str.= $this->get_course_row($course, $teacherNumber, $courseNumber);
            $courseNumber++;
        }

        return $str;
    }

    private function get_course_row(\stdClass $course, int $teacherNumber, int $courseNumber) : string 
    {
        $attr = array(
            'id' => self::TEACHER_ID.$teacherNumber.self::COURSE_ID.$courseNumber,
            'data-teacher-number' => self::TEACHER_ID.$teacherNumber,
            'class' => 'course-row hidden',
            'onclick' => 'toggle_items_rows_visibility(this)',
            'title' => get_string('more_info_title', 'report_averagechecktime')
        );
        $str = \html_writer::start_tag('tr', $attr);

        $attr = array('style' => 'padding-left:25px');
        $text = $courseNumber.'. '.$course->name;
        $str.= \html_writer::tag('td', $text, $attr);

        // Module cell
        $str.= \html_writer::tag('td', '');

        $attr = array('class' => 'tac');
        $text = round($course->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $course->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        $str.= $this->get_items_rows($course, $teacherNumber, $courseNumber);

        return $str;
    }

    private function get_items_rows(\stdClass $course, int $teacherNumber, int $courseNumber) : string 
    {
        $str = '';

        $itemNumber = 1;
        foreach($course->items as $item)
        {
            $str.= $this->get_item_row($item, $teacherNumber, $courseNumber, $itemNumber);
            $itemNumber++;
        }

        return $str;
    }

    private function get_item_row(\stdClass $item, int $teacherNumber, int $courseNumber, int $itemNumber) : string 
    {
        $attr = array(
            'data-course-number' => self::TEACHER_ID.$teacherNumber.self::COURSE_ID.$courseNumber,
            'data-teacher-number-of-item' => self::TEACHER_ID.$teacherNumber,
            'class' => 'item-row hidden'
        );
        $str = \html_writer::start_tag('tr', $attr);

        $attr = array('style' => 'padding-left:50px');
        $text = $itemNumber.'. '.$item->name;
        $str.= \html_writer::tag('td', $text, $attr);

        // Module cell
        $attr = array('class' => 'tac');
        $str.= \html_writer::tag('td', $item->module, $attr);

        $attr = array('class' => 'tac');
        $text = round($item->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $item->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        return $str;
    }

    private function get_forms() : string 
    {
        $forms = $this->get_sort_by_grade_form(Main::SORT_BY_NAME);
        $forms.= $this->get_sort_by_grade_form(Main::SORT_BY_GRADE);
        $forms.= $this->get_sort_by_grade_form(Main::SORT_BY_TIME);
        $forms.= $this->get_period_form();

        return $forms;
    }

    private function get_sort_by_grade_form(string $sortType) : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::SORT_TYPE,
            'value' => $sortType
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'method' => 'post',
            'id' => $sortType.self::_FORM,
            'class' => 'hidden'
        );
        return \html_writer::tag('form', $params, $attr);
    }

    private function get_period_form() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::SORT_TYPE,
            'value' => $this->sortType
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'method' => 'post',
            'id' => self::PERIOD_FORM,
            'class' => 'hidden'
        );
        return \html_writer::tag('form', $params, $attr);
    }

}
