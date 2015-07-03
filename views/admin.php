<!-- This file is used to markup the administration form of the widget. -->
<?php
$xml = simplexml_load_file("http://bcs.tandemcal.com/index.php?type=export&action=xml&extra_fields=school_name,department_name");
$school_ids = array_unique($xml->xpath("/tfs_events/event/schools/id"));
$school_ids = json_decode(json_encode((array)$school_ids),1);
$school_ids = array_reduce($school_ids, 'array_merge', array());

$school_names = array_unique($xml->xpath("/tfs_events/event/schools/name"));
$school_names = json_decode(json_encode((array)$school_names),1);
$school_names = array_reduce($school_names, 'array_merge', array());

$department_ids = array_unique($xml->xpath("/tfs_events/event/departments/id"));
$department_ids = json_decode(json_encode((array)$department_ids),1);
$department_ids = array_reduce($department_ids, 'array_merge', array());

$department_names = array_unique($xml->xpath("/tfs_events/event/departments/department_name"));
$department_names = json_decode(json_encode((array)$department_names),1);
$department_names = array_reduce($department_names, 'array_merge', array());
?>
<div class="option">
    <label for="<?php echo $this->get_field_id( 'school' ); ?> "><?php _e('School:', 'school'); ?></label>
    <select id="<?php echo $this->get_field_id( 'school' ); ?>" name="<?php echo $this->get_field_name( 'school' ); ?>">
        <option value=""></option>
        <?php
        foreach($school_names as $key => $school_name) {
            $school_name = htmlspecialchars($school_name); ?>
            <option value="<?php echo $school_ids[$key]; ?>"<?php if ($instance['school'] == $school_ids[$key]){ echo ' selected="selected"'; } ?>><?php echo $school_name; ?></option>
        <?php } ?>
    </select>
</div>
<div class="option">
    <label for="<?php echo $this->get_field_id( 'department' ); ?> "><?php _e('Department:', 'department'); ?></label>
    <select id="<?php echo $this->get_field_id( 'department' ); ?>" name="<?php echo $this->get_field_name( 'department' ); ?>">
        <option value=""></option>
        <?php
        foreach($department_names as $key => $department_name) {
            $department_name = htmlspecialchars($department_name); ?>
            <option value="<?php echo $department_ids[$key]; ?>"<?php if ($instance['department'] == $department_ids[$key]){ echo ' selected="selected"'; } ?>><?php echo $department_name; ?></option>
        <?php } ?>
    </select>
</div>