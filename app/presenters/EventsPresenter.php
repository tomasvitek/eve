<?php

namespace App\Presenters;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


class EventsPresenter extends Nette\Application\UI\Presenter
{

	/** @var Model\EventsRepository */
	private $events;


	protected function checkIfLoggedIn() 
	{
		if (!$this->getUser()->isLoggedIn()) {
			if ($this->getUser()->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
			}
			$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
		}
	}


	public function __construct(Model\EventsRepository $events)
	{
		$this->events = $events;
	}

	/********************* view default *********************/

	public function renderDefault()
	{
		$this->template->title = 'Upcoming Events';
		$this->template->events = $this->events->findUpcoming();
	}

	/********************* view feed *********************/

	public function renderFeed()
	{
		$this->template->events = $this->events->findUpcoming();
	}


	/********************* view past *********************/

	public function renderPast()
	{
		$this->template->title = 'Past Events';
		$this->template->events = $this->events->findPast();
	}


	/********************* view attend *********************/

	public function renderAttend($id)
	{
		$this->events->addAttending($id);
		$this->flashMessage('Thanks for your RSVP!');
		$this->redirect('default');		
	}


	/********************* view add *********************/

	public function actionAdd()
	{
		$this->checkIfLoggedIn();	

		$this['albumForm']['save']->caption = 'Add';
	}


	/********************* view edit *********************/

	public function actionEdit($id = 0)
	{
		$this->checkIfLoggedIn();

		$form = $this['eventForm'];
		if (!$form->isSubmitted()) {
			$event = $this->events->findById($id);
			if (!$event) {
				$this->error('Event not found');
			}
			$form->setDefaults($event);
		}
	}


	/********************* view delete *********************/

	public function renderDelete($id = 0)
	{
		$this->checkIfLoggedIn();	

		$this->template->event = $this->events->findById($id);
		if (!$this->template->event) {
			$this->error('Event not found');
		}
	}

	/********************* component factories *********************/

	/**
	 * Event edit form factory
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentEventForm()
	{
		$form = new Form;
		$form->addText('speaker', 'Speaker:')
			->setRequired('Please enter the speaker\'s name.');

		$form->addText('website', 'Website:')
			->setRequired(FALSE)
			->addRule(Form::URL, 'Please enter a valid URL.');

		$form->addText('institution', 'Institution:');

		$form->addText('topic', 'Topic:')
			->setRequired('Please enter the topic.');

		$form->addTextArea('abstract', 'Abstract:')
			->setRequired('Please enter the abstract.');

		$form->addText('location', 'Location:');

		$form->addDateTimePicker('timestart', 'Start:');

		$form->addDateTimePicker('timeend', 'End:');

		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = [$this, 'eventFormSucceeded'];
		return $form;
	}

	public function eventFormSucceeded($form, $values)
	{
		$values = $form->getValues();
		$id = (int) $this->getParameter('id');
		if ($id) {
			$this->events->findById($id)->update($values);
			$this->flashMessage('The event has been updated.');
		} else {
			$this->events->insert($values);
			$this->flashMessage('The event has been added.');
		}
		$this->redirect('Events:');
	}



	/**
	 * Delete form factory.
	 * @return Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = [$this, 'formCancelled'];

		$form->addSubmit('delete', 'Delete')
			->setHtmlAttribute('class', 'default')
			->onClick[] = [$this, 'deleteFormSucceeded'];

		$form->addProtection();
		return $form;
	}


	public function deleteFormSucceeded()
	{
		$this->events->findById($this->getParameter('id'))->delete();
		$this->flashMessage('Event has been deleted.');
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->redirect('default');
	}

}
