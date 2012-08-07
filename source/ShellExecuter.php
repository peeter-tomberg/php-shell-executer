<?php
/**
 * Executes a shell command and kills it if it runs too long
 * @author Peeter Tomberg
 *
 */
class ShellExecuter
{

	CONST DEFAULT_EXECUTE_TIMEOUT = 5;

	/**
	 * Command to execute
	 * @var String
	 */
	private $command;
	/**
	 * The timeout given for the function
	 */
	private $timeout;
	/**
	* This points to the process we start
	**/
	private $resource;
	/**
	* This is where we write different files
	**/
	private $files = array();
	/**
	* The pid of the process that is actually backgrounded
	**/
	private $pid;
	/**
	 *
	 * Construct the ShellExecuter
	 * @param string $command - the command to execute
	 * @param integer $timeout - the default timeout
	 */
	function __construct($command, $timeout = self::DEFAULT_EXECUTE_TIMEOUT) {

		$this->command = $command;
		$this->timeout = $timeout;
	}
	/**
	 * Executes the command (blocking)
	 * @throws Exception when timeout reached
	 */
	public function execute() {

		$unique_id = uniqid();

		$sleeptime = 100000;
		$looptime = $this->timeout * 1000000 / $sleeptime;

		$this->files["pid"] 	= sys_get_temp_dir() . '/shellexecuter_pid' . $unique_id . '.txt';
		$this->files["success"] = sys_get_temp_dir() . '/shellexecuter_success' . $unique_id . '.txt';

		$descriptorspec    = array(
            1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
	    $this->resource = proc_open("(" . $this->command . " && touch ".$this->files['success'].") & echo $! > ".$this->files['pid']." &" , $descriptorspec, $this->pipes, null, $_ENV);
		/**
		* Lets get the actual pid of the process we're backgrounding
		* Since PHP doesn't run our process right away, lets sleep until we actually have a pid
		**/
		while(!$this->pid) {
			if(file_exists($this->files["pid"])) {
				$pid = (int)file_get_contents($this->files["pid"]);
				if($pid > 0)
					$this->pid = $pid;
			}
			else
				usleep($sleeptime);
		}

		for($i = 0; $i <= $looptime; $i++) {

			if($this->isRunning()) {
				if($i == $looptime) {
					$this->kill();
				}
				usleep($sleeptime);
			}
			else
				break;
		}
		/**
		* Lets gather some info from the pipes
		**/
		$stdout = stream_get_contents($this->pipes[1]);
		$stderr = stream_get_contents($this->pipes[2]);

		/**
		* If we didn't touch the success file, the processes executed with a failure
		**/
		if(!file_exists($this->files["success"])) {
			throw new Exception("Command executed with failure: " . $stderr);
		}

		return $stdout;
	}

	/**
	 * Cleans up and throws an exception
	 * @param string $reason
	 * @throws Exception
	 */
	private function fail($reason) {
		$this->cleanup();
		throw new Exception($reason);
	}

	/**
	 * Determines if the pid is running
	 */
	private function isRunning(){
		if($this->pid == null)
			$this->fail("Pid not defined");

        return file_exists( "/proc/$this->pid" );
	}
	/**
	 * Kills this process
	 */
	private function kill() {
    	proc_terminate($this->resource);
		shell_exec("kill -9 " . $this->pid);
		$this->fail("Exec timeout reached, process (".$this->pid.") killed. Command: " .$this->command);
	}
	/**
	* Lets remove all our created files
	**/
	private function cleanup() {
		foreach($this->files as $file) {
			@unlink($file);
		}
	}



}