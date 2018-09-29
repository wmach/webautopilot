<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//_/
//_/
//_/
{
    $self = new Fork();
    $self->loop();
}

class Fork{
    private $counter = 0;

    function __construct(){
        $this->counter = 10;
    }

    function loop(){
        declare(ticks = 1) {
            pcntl_signal(SIGALRM, array(get_class($this),'sig_handler'));
            posix_kill(posix_getpid(),SIGALRM);
            while($this->counter > 0);
        }
    }

    public function sig_handler($signo)
    {
        pcntl_alarm( 1 );

        $this->counter--;

        $formatter='Y/m/d H:i:s';
        $pid = pcntl_fork();
        if ($pid == -1){
            die("error: \$pid=$pid\n");
        }else if ($pid){
            pcntl_wait($status);
        }else{
            echo posix_getpid()."; ";
            echo date($formatter)."; hello, world!\n";
        }
    }
}
?>
