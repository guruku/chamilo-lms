<?php
/* For licensing terms, see /license.txt */

/**
 * Class OralExpression
 * This class allows to instantiate an object of type FREE_ANSWER,
 * extending the class question
 * @author Eric Marguin
 *
 * @package chamilo.exercise
 */
class OralExpression extends Question
{
    static $typePicture = 'audio_question.png';
    static $explanationLangVar = 'OralExpression';

    private $sessionId;
    private $userId;
    private $exerciseId;
    private $exeId;
    private $storePath;
    private $fileName;
    private $filePath;

    public $available_extensions = array('wav', 'ogg');

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this -> type = ORAL_EXPRESSION;
        $this -> isContent = $this-> getIsContent();
    }

    /**
     * function which redefine Question::createAnswersForm
     * @param FormValidator $form
     */
    function createAnswersForm($form)
    {

        $form -> addElement('text','weighting', get_lang('Weighting'), array('class' => 'span1'));
        global $text, $class;
        // setting the save button here and not in the question class.php
        $form->addButtonSave($text, 'submitQuestion');
        if (!empty($this->id)) {
            $form -> setDefaults(array('weighting' => float_format($this->weighting, 1)));
        } else {
            if ($this -> isContent == 1) {
                $form -> setDefaults(array('weighting' => '10'));
            }
        }
    }

    /**
     * abstract function which creates the form to create / edit the answers of the question
     * @param the FormValidator $form
     */
    function processAnswersCreation($form)
    {
        $this->weighting = $form ->getSubmitValue('weighting');
        $this->save();
    }

    /**
     * @param null $feedback_type
     * @param null $counter
     * @param null $score
     * @return null|string
     */
    function return_header($feedback_type = null, $counter = null, $score = null)
    {
        $header = parent::return_header($feedback_type, $counter, $score);
        $header .= '<table class="'.$this->question_table_class.'">
            <tr>
                <th>&nbsp;</th>
            </tr>
            <tr>
                <th>'.get_lang("Answer").'</th>
            </tr>
            <tr>
                <th>&nbsp;</th>
            </tr>';

        return $header;
    }

    /**
     * initialize the attributes to generate the file path
     * @param $sessionId integer
     * @param $userId integer
     * @param $exerciseId integer
     * @param $exeId integer
     */
    public function initFile($sessionId, $userId, $exerciseId, $exeId)
    {
        $this->sessionId = intval($sessionId);
        $this->userId = intval($userId);

        $this->exerciseId = 0;

        if (!empty($exerciseId)) {
            $this->exerciseId = intval($exerciseId);
        }

        $this->exeId = intval($exeId);

        $this->storePath = $this->generateDirectory();
        $this->fileName = $this->generateFileName();
        $this->filePath = $this->storePath . $this->fileName;
    }

    /**
     * Generate the necessary directory for audios. If them not exists, are created
     * @return string
     */
    private function generateDirectory()
    {
        $this->storePath = api_get_path(SYS_COURSE_PATH) . $this->course['path'] . '/exercises/';

        if (!is_dir($this->storePath)) {
            mkdir($this->storePath);
        }

        if (!is_dir($this->storePath . $this->sessionId)) {
            mkdir($this->storePath . $this->sessionId);
        }

        if (!empty($this->exerciseId) && !is_dir($this->storePath . $this->sessionId . '/' . $this->exerciseId)) {
            mkdir($this->storePath . $this->sessionId . '/' . $this->exerciseId);
        }

        if (!empty($this->id) && !is_dir($this->storePath . $this->sessionId . '/' . $this->exerciseId . '/' . $this->id)) {
            mkdir($this->storePath . $this->sessionId . '/' . $this->exerciseId . '/' . $this->id);
        }

        if (!empty($this->userId) && !is_dir($this->storePath . $this->sessionId . '/' . $this->exerciseId . '/' . $this->id . '/' . $this->userId)) {
            mkdir($this->storePath . $this->sessionId . '/' . $this->exerciseId . '/' . $this->id . '/' . $this->userId);
        }

        return $this->storePath .= implode(
                '/',
                array(
                    $this->sessionId,
                    $this->exerciseId,
                    $this->id,
                    $this->userId
                )
            ) . '/';
    }

    /**
     * Generate the file name
     * @return string
     */
    private function generateFileName()
    {
        return implode(
            '-',
            array(
                $this->course['real_id'],
                $this->sessionId,
                $this->userId,
                $this->exerciseId,
                $this->id,
                $this->exeId
            )
        );
    }

    /**
     * Generate a relative directory path
     * @return string
     */
    private function generateRelativeDirectory()
    {
        return '/exercises/' . implode(
            '/',
            [$this->sessionId, $this->exerciseId, $this->id, $this->userId]
        ) . '/';
    }

    /**
     * Return the HTML code to show the RecordRTC/Wami recorder
     * @return string
     */
    public function returnRecorder()
    {
        $directory = '/..' . $this->generateRelativeDirectory();

        $recordAudioView = new Template('', false, false,false, false, false, false);
        $recordAudioView->assign('directory', $directory);
        $recordAudioView->assign('user_id', $this->userId);
        $recordAudioView->assign('file_name', $this->fileName);

        $template = $recordAudioView->get_template('exercise/oral_expression.tpl');

        return $recordAudioView->fetch($template);
    }

    /**
     * Get the absolute file path. Return null if the file doesn't exists
     * @param bool $loadFromDatabase
     * @return string
     */
    public function getAbsoluteFilePath($loadFromDatabase = false)
    {
        $fileName = $this->fileName;

        if ($loadFromDatabase) {
            $em = Database::getManager();
            //Load the real filename just if exists
            if (isset($this->exeId, $this->userId, $this->id, $this->sessionId, $this->course['real_id'])) {
                $result = $em
                    ->getRepository('ChamiloCoreBundle:TrackEAttempt')
                    ->findOneBy([
                        'exeId' => $this->exeId,
                        'userId' => $this->userId,
                        'questionId' => $this->id,
                        'sessionId' => $this->sessionId,
                        'cId' => $this->course['real_id']
                    ]);

                if (!$result) {
                    return null;
                }

                return $this->storePath . $result->getFilename();
            }
        }

        foreach ($this->available_extensions as $extension) {
            if (!is_file($this->storePath . $fileName . ".$extension.$extension")) {
                continue;
            }

            return "{$this->storePath}$fileName.$extension.$extension";
        }

        return null;
    }

    /**
     * Get the URL for the audio file. Return null if the file doesn't exists
     * @return string
     */
    public function getFileUrl()
    {
        $filePath = $this->getAbsoluteFilePath();

        if (empty($filePath)) {
            return null;
        }

        return str_replace(
            api_get_path(SYS_COURSE_PATH),
            api_get_path(WEB_COURSE_PATH),
            $filePath
        );
    }

    /**
     * Tricky stuff to deal with the feedback = 0 in exercises (all question per page)
     * @param $exe_id integer
     */
    public function replaceWithRealExe($exe_id)
    {
        $filename = null;
        //ugly fix
        foreach ($this->available_extensions as $extension) {
            $items = explode('-', $this->fileName);
            $items[5] = 'temp_exe';
            $filename = implode('-', $items);

            if (is_file($this->storePath . $filename . '.' . $extension)) {
                $old_name = $this->storePath . $filename . '.' . $extension;
                $items = explode('-', $this->fileName);
                $items[5] = $exe_id;
                $filename = $filename = implode('-', $items);
                $new_name = $this->storePath . $filename . '.' . $extension;
                rename($old_name, $new_name);
                break;
            }
        }
    }
}
