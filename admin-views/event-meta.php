<?php if ( empty($customFields) ) {
	return;
} ?>
<table id='event-meta' class='eventtable'>
   <tbody>
      <tr><td colspan='2' class='snp_sectionheader'><h4>Event Custom Fields</h4></td></tr>
      <?php foreach ($customFields as $customField): ?>
         <?php $val = get_post_meta(get_the_ID(), $customField['name'], true) ?>
         <tr>
            <td><?php echo $customField['label'] ?></td>
            <td>
               <?php $options = explode("\n", $customField['values']) ?> 
               <?php if($customField['type'] == 'text'): ?>
                  <input type='text' name='<?php echo $customField['name']?>' value='<?php echo $val ?>'/>
               <?php elseif($customField['type'] == 'radio'): ?>
                  <?php foreach ($options as $option): ?>
                     <div><label><input type='radio' name='<?php echo $customField['name']?>' value='<?php echo $option ?>' <?php checked(trim($val), trim($option)) ?>/> <?php echo $option ?></label></div>
                  <?php endforeach ?>
               <?php elseif($customField['type'] == 'checkbox'): ?>
                  <?php foreach ($options as $option): ?>
                     <?php $values = explode("|", $val); ?>
                     <div><label><input type='checkbox' value='<?php echo trim($option) ?>' <?php checked(in_array(trim($option), $values)) ?> name='<?php echo $customField['name']?>[]'/> <?php echo $option ?></label></div>
                  <?php endforeach ?>
               <?php elseif($customField['type'] == 'dropdown'): ?>
                  <select name='<?php echo $customField['name']?>'>
                     <?php $options = explode("\n", $customField['values']) ?> 
                     <?php foreach ($options as $option): ?>
                       <option value='<?php echo $option ?>' <?php selected($val, $option) ?>><?php echo $option ?></option>
                     <?php endforeach ?>
                  </select>
               <?php elseif($customField['type'] == 'textarea'): ?>
                  <textarea name='<?php echo $customField['name']?>'><?php echo $val ?></textarea>
               <?php endif; ?>
           </td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>
