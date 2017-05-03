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

	public function renderICal()
	{
    $this->absoluteUrls = TRUE;

    $calendar = new \Eluceo\iCal\Component\Calendar($this->link('Events:'));
    $calendar->setName("Events calendar");
    //$calendar->setDescription("Calendar description");

    foreach ($this->events->findUpcoming() as $event) {
      if ($event->timestart) {
				$e = new \Eluceo\iCal\Component\Event();
				$summary = '"' . $event->topic . '" by ' .$event->speaker;
				if ($event->institution) {
          $summary .= ' (' . $event->institution . ')';
        }
				$e->setSummary($summary);
				$e->setUrl($this->link('Events:') . '#event-' . $event->id);
				if ($event->location) {
					$e->setLocation($event->location);
				}

				$description = "Topic: " . $event->topic . "\n";
				$description .= "Speaker: " . $event->speaker;
				if ($event->institution) {
					$description .= " (" . $event->institution . ")";
				}

				if ($event->timestart) {
					$description .= "\nDate: " . date("d.m.Y", $event->timestart) . ", " . date("H:i", $event->timestart) . " - " . date("H:i", $event->timeend);
				}
				if ($event->location) {
					$description .= "\nLocation: " . $event->location;
				}
				$description .= "\n\nAbstract:\n" . $event->abstract;

				$e->setDescription(strip_tags($description));
        $e->setDtStart(new \DateTime($event->timestart));
        $e->setDtEnd(new \DateTime($event->timeend));
				$e->setUseUtc(false);
        $calendar->addComponent($e);
      }
    }

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=events.ics');
    $response = new \Nette\Application\Responses\TextResponse($calendar->render());
    $this->sendResponse($response);
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
		$this->events->addAttending(base64_decode($id));
		$this->flashMessage('Thanks for your RSVP!');
		$this->redirect('default');
	}


	/********************* view add *********************/

	public function actionAdd()
	{
		$this->checkIfLoggedIn();

		$this['eventForm']['save']->caption = 'Add';
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

		$form->addSubmit('save', 'Save');

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
