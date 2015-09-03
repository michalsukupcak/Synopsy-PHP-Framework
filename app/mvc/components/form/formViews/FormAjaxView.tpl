<h3><strong>{getString key="form.response.ajax"}</strong></h3>
<h4>{date('H:i:s, j.n.Y')}</h4>
{getString key="form.response.inputText"}: <b>{$form->getElement('inputText')->getPostValue()}</b><br>
{getString key="form.response.inputInteger"}: <b>{$form->getElement('inputInteger')->getPostValue()}</b><br>
{getString key="form.response.select"}: <b>{$form->getElement('select')->getPostValue()}</b><br>
{getString key="form.response.checkbox"}: <b>{$form->getElement('checkbox')->getPostValue()}</b><br>