<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 13:47, 10. 5. 2015
 */

namespace App\WebModule\Presenters;


use App\WebModule\Model\Mailer;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

/**
 * Class ContactPresenter
 * @package App\WebModule\Presenters
 */
class ContactPresenter extends BasePresenter
{

	/**
	 * @return Form
	 */
	public function createComponentContactForm()
	{
		$form = new Form();
		$form->addProtection();
		$form->addText('first_name')->setRequired('Musíte zadat své jméno.');
		$form->addText('last_name')->setRequired('Musíte zadat své příjmení.');
		$form->addText('phone')->setRequired('Musíte zadat telefon.')->addRule(FORM::PATTERN, 'Neplatné telefoní číslo.', '[0-9]{9}');
		$form->addText('email')->setRequired('Musíte zadat email.')->addRule(FORM::EMAIL, 'Neplatný email.');
		$form->addTextArea('text')->setRequired('Musíte zadat váš požadavek.');
		$form->addCheckbox('do_not_call');
		$form->addSubmit('send');
		$form->onSuccess[] = [$this, 'ContactFormSuccess'];

		return $form;
	}

	/**
	 * @param Form $form
	 */
	public function ContactFormSuccess(Form $form)
	{
		$values = $form->getValues();

		try {
			$mailer = new Mailer();
			$mailer->setHtmlBody(
				__DIR__ . '/templates/newOrder.latte',
				[
					'first_name'  => ucfirst($values['first_name']),
					'last_name'   => ucfirst($values['last_name']),
					'phone'       => $values['phone'],
					'email'       => $values['email'],
					'do_not_call' => $values['do_not_call'],
					'text'        => $values['text'],
					'timestamp'   => time(),
				]
			)
				->addTo('jan.kotrbaa@gmail.com')
				->addTo('bucek.p@email.cz')
				->setFrom("kontakt@sweetbike4you.cz")
				->setSubject("Dotaz od " . ucfirst($values['first_name']) . " " . ucfirst($values['last_name']) . " na SweetBike4You");
			$mailer->sendEmail();
			$this->flashMessage('Váš vzkaz byl úspěšne odeslán, brzy Vás budeme kontaktovat.', 'success');
		} catch (\Exception $e) {
			$this->flashMessage('Váš vzkaz se bohužel nepodařilo odeslat, můžete nás kontaktovat přímo.', 'error');
		}
	}
}