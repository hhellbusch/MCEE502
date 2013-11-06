<?php

class CmosProcess
{
	private $code;
	private $deviceName;
	public function __construct()
	{
		$this->code = '';
	}

	private function append($str)
	{
		$this->code = $this->code . $str . "\n";
	}
	private function endl()
	{
		$this->code = $this->code . "\n";
	}
	private function comment($str)
	{
		$this->append('# '.$str);
	}



	public function getNmosUpToKoi()
	{
		$this->reset();
		$this->deviceName = 'nmos';
		$this->initMesh();
		$this->growPadOxide(4);
		$this->implantPWell();
		$this->wellDriveIn();
		$this->etchAllOxide(16);
		$this->growPadOxide(17);
		$this->locosTwoOxideWithoutGrowth();
		$this->etchAllOxide(29);
		$this->growKoiOxide();
		$this->closeSimulator();
		return $this->code;
	}

	public function getNmosPostKoi()
	{
		$this->reset();
		$this->deviceName = 'nmos';
		$this->append('go athena');
		$this->append('init infile=./structures/'
				.$this->deviceName.'_step30_post_koi.str');
		$this->append("method grid.oxide=0.01 gridinit.ox=0.01");
		$this->vtAdjust();
		$this->etchAllOxide('33');
		$this->growGateOx();
		$this->depositPolyGate();
		$this->dopePolyGate();
		$this->etchPolyGate();
		$this->nppSourceDrainImplant();
		$this->polyReoxidation();
		$this->ltoDeposition();
		$this->sourceDrainAnneal();
		$this->sourceDrainContact();
		$this->extractFinalValues();
		$this->finalizeDevice();

		$this->electricalSimulation();
		$this->closeSimulator();

		return $this->code;

	}

	public function getPmosUpToKoi()
	{
		$this->reset();
		$this->deviceName = 'pmos';
		$this->initMesh();
		$this->growPadOxide(4);
		$this->implantNWell();
		$this->locosOxideGrowth();
		$this->wellDriveIn();
		$this->etchAllOxide(16);
		$this->growPadOxide(17);
		$this->locosTwoOxideWithoutGrowth();
		$this->etchAllOxide(29);
		$this->growKoiOxide();
		$this->closeSimulator();
		return $this->code;
	}

	public function getPmosPostKoi()
	{
		$this->reset();
		$this->deviceName = 'pmos';
		$this->append('go athena');
		$this->append('init infile=./structures/'.$this->deviceName
					.'_step30_post_koi.str');
		$this->append("method grid.oxide=0.01 gridinit.ox=0.01");
		$this->vtAdjust();
		$this->etchAllOxide(33);
		$this->growGateOx();
		$this->depositPolyGate();
		$this->dopePolyGate();
		$this->etchPolyGate();
		$this->pppSourceDrainImplant();
		$this->polyReoxidation();
		$this->ltoDeposition();
		$this->sourceDrainAnneal();
		$this->sourceDrainContact();
		$this->extractFinalValues();
		$this->finalizeDevice();

		$this->electricalSimulation();
		$this->closeSimulator();

		return $this->code;

	}

	public function getAtlasCodeLengthDIBL($deviceName)
	{
		$this->reset();
		$this->deviceName = $deviceName;
		$this->append('go athena');
		$this->append('init infile=./structures/'.$this->deviceName.'_1.4e+12_step58_post_al_etch.str');
		$this->append('set length = 1.3');
		$this->append('etch right p1.x=($"length"/2+0.7)');

		$this->append('structure outfile=./structures/afterEtch_$"length".str');
		$this->append('structure mirror right');
		$this->append('structure outfile=./structures/afterEtch_mirror_$"length".str');
		$this->endl();

		$this->append('electrode name=gate x=$"length" y=-0.2');
		$this->append('electrode name=source x=0.1 y=-0.5');
		$this->append('electrode name=drain x=($"length"+1.3) y=-0.2');
		$this->append('electrode name=substrate backside');
		$this->append('structure outfile=./structures/length_$"length"_final.str');
		$this->append('extract name="--$\'length\'--"');
		$this->electricalSimulationWithDIBL();
		$this->closeSimulator();

		return $this->code;
	}

	public function reset()
	{
		$this->code = '';
	}

	private function initMesh()
	{
		$this->append("go athena");
		$this->append("line x loc=0.00 spac=0.10");
		$this->append("line x loc=0.15 spac=0.01");
		$this->append("line x loc=0.50 spac=0.01");
		$this->append("line x loc=0.60 spac=0.02");
		$this->append("line x loc=1.35 spac=0.02");
		$this->endl();

		$this->append("line y loc=0.00 spac=0.02");
		$this->append("line y loc=0.20 spac=0.02");
		$this->append("line y loc=1.00 spac=0.05");
		$this->append("line y loc=2.00 spac=0.10");
		$this->append("line y loc=6.00 spac=0.10");
		$this->endl();

		$this->append("extract name=\"---new ".$this->deviceName." simulation--\"");
		$this->endl();
		$this->append("init silicon boron resistivity=10 orientation=100 space.mult=2");

		$this->append("method grid.oxide=0.01 gridinit.ox=0.01");
		$this->endl();
	}

	private function growPadOxide($stepNum)
	{
		$this->comment("recipe Recipe 458 or 250 ");
		$this->comment("target oxide thickness is 500A");
		$this->comment("pad oxide");

		$this->append('diffus time=15 temp=800 nitro');
		$this->append("diffus time=20 temp=800 t.final=1000 dryo2");
		$this->append("diffus time=53 temp=1000 dryo2");
		$this->append("diffus time=5 temp=1000 nitro");
		$this->endl();

		$this->append('extract name="Xox_pad_step'.$stepNum.'" thickness material="SiO~2" mat.occno=1 x.val=.5');
		$this->append('struct outfile=./structures/'.$this->deviceName.'_step'.$stepNum.'_pad_oxide.str');
		$this->endl();
	}

	private function implantPWell()
	{
		$this->comment("make the p well");
		$this->append("implant boron dose=6e12 energy=55 tilt=7 rotation=45 crystal");
		$this->append("struct outfile=./structures/".$this->deviceName."_step14_post_p_well_implant.str");
		$this->endl();
	}

	private function implantNWell()
	{
		$this->comment("make the n well");
		$this->append("implant phosphor dose=5.5e12 energy=100 tilt=7 rotation=45 crystal");
		$this->append("struct outfile=./structures/".$this->deviceName."_step14_post_n_well_implant.str");
		$this->endl();
	}

	private function locosOxideGrowth()
	{
		$this->comment("Recipe 350 (according to Hirschman)");
		$this->comment("target oxide thickness is 5000");
		$this->append("diffus time=130 temp=950 weto2");
		$this->append('extract name="Xox_LOCOS_1" thickness material="SiO~2" mat.occno=1 x.val=5');
		$this->endl();
	}

	private function wellDriveIn()
	{
		$this->comment("recipe 162 - well drive in");
		$this->comment("step 15 on process flow");
		
		$this->append("diffus time=20 temp=800 nitro");
		$this->append("diffus time=30 temp=800 t.final=1100 nitro");
		$this->append("diffus time=1560 temp=1100 nitro");
		$this->append("diffus time=45 temp=1100 t.final=800 nitro");
		$this->append("struct outfile=./structures/".$this->deviceName."_step15_post_well_drive_in.str");
		$this->endl();
	}

	private function etchAllOxide($stepNum)
	{
		$this->comment('step ' . $stepNum);
		$this->append("etch oxide all");
		$this->append("struct outfile=./structures/".$this->deviceName."_step".$stepNum."_oxide_removed.str");
		$this->endl();
	}

	private function growKoiOxide()
	{
		$this->comment("grow koi oxide");
		$this->comment("recipe 311");
		$this->append("diffus time=15 temp=800 nitro"); //stabalize
		$this->append("diffus time=10 temp=800 t.final=900 nitro"); //ramp up
		$this->append("diffus time=5 temp=900 dryo2"); //o2 purge 
		$this->append("diffus time=42 temp=900 weto2"); //soak
		$this->append("diffus time=20 temp=900 t.final=800 nitro"); //n2 purge
		//target is 1000
		$this->append('extract name="Xox_koi" thickness material="SiO~2" mat.occno=1 x.val=.5');
		$this->append("struct outfile=./structures/".$this->deviceName."_step30_post_koi.str");
		$this->endl();
	}

	private function vtAdjust()
	{
		$this->comment("vt adjust");
		$this->append("set vtAdjustDose = 1.4e12");
		$this->append('implant boron dose=$"vtAdjustDose" energy=65 tilt=7 rotation=45 crystal');
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step31_vt_adjust.str');
		$this->endl();
	}

	



	private function growGateOx()
	{
		$this->comment("recipe 474");
		$this->comment("step 35");
		$this->append("diffus time=12 temp=800 dryo2");
		$this->append("diffus time=20 temp=800 dryo2");
		$this->append("diffus time=20 temp=800 t.final=1000 dryo2");
		$this->append("diffus time=5 temp=1000 dryo2 hcl.pc=0.5"); //stabalized
		$this->append("diffus time=9.5 temp=1000 dryo2 hcl.pc=0.5");  //soak
		$this->append("diffus time=5 temp=1000 dryo2"); //o2 purge
		$this->endl();

		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step35_post_gate_ox.str');
		$this->append('extract name="Xox_gate" thickness material="SiO~2" mat.occno=1 x.val=.5');
		$this->endl();
	}

	private function depositPolyGate()
	{
		$this->comment("step 36");
		$this->append("deposit polysilicon thick=0.60 divisions=20");
		$this->endl();
	}

	private function dopePolyGate()
	{
		$this->comment("step 37 - poly dope");
		$this->append("implant phosphor dose=2e16 energy=50 tilt=7 rotation=45 crystal");
		$this->endl();
	}

	private function etchPolyGate()
	{
		$this->comment("step 40");
		$this->append("etch polysilicon left x=0.35");
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step40_post_gate_dep.str');
		$this->endl();
	}

	private function nppSourceDrainImplant()
	{
		$this->comment("step 43 N+ s/d implant");
		$this->append("implant phosphor dose=2e15 energy=75 tilt=7 rotation=45 crystal");
		$this->endl();
	}

	private function pppSourceDrainImplant()
	{
		$this->comment("step 46 P+ s/d implant");
		$this->append("implant boron dose=2e15 energy=40 tilt=7 rotation=45 crystal");
		$this->endl();
	}

	private function polyReoxidation()
	{
		$this->comment("step 49 - reox");
		$this->comment("furnace recipe 175");
		#              time     temp 	gas
		# push in      12 min   2     2
		# stabilize    2  min   2     2
		# ramp up      5  min   7     1
		# stabilize    10 min   7     1
		# soak         15 min   7     6
		# n2 purge     10 min   7     3
		# ramp down    10 min   1     2

		$this->append("diffus time=14 temp=800 nitro");
		$this->append("diffus time=5 temp=800 t.final=850 nitro");
		$this->append("diffus time=10 temp=850 nitro");
		$this->append("diffus time=15 temp=850 weto2");
		$this->append("diffus time=10 temp=850 nitro");
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step49_post_gate_reox.str');
		$this->endl();
	}

	public function ltoDeposition()
	{
		$this->comment('post gate reox');
		$this->append("deposit oxide thick=0.30");
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step50_lto_dep.str');
		$this->endl();
	}

	private function locosTwoOxideWithoutGrowth()
	{
		//does the oxide temp in nitro this is because
		//this oxide is not grown in the active region
		$this->comment("step 26");
		$this->append('diffus time=300 temp=950 nitro');
		$this->append('struct outfile=./structures/'.$this->deviceName.'_step26_locos_fox_nitro.str');
		$this->endl();
	}

	public function sourceDrainAnneal()
	{
		#recipe 144
		#					20 min, temp row 2,	gas row 2, 
		# ramp up	20 min,	temp row 3,	gas row 1
		# stabilize 	10 min,	temp row 3, gas row 1
		# wet soak   10 min,	temp row 3, gas row 6
		# dry soak	5 min	temp row 3, gas row 5
		# n2 purge	5 min	temp row 2, gas row 3
		# ramp down  35 min	temp row 2, gas row 2 - ignored

		#source/drain anneal
		
		$this->append("diffus time=20 temp=800 nitro");
		$this->append("diffus time=20 temp=800 t.final=1000 nitro");
		$this->append("diffus time=10 temp=1000 nitro");
		$this->append("diffus time=10 temp=1000 weto2");
		$this->append("diffus time=5  temp=1000 dryo2");
		$this->append("diffus time=5  temp=1000 nitro");

		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step51_post_sd_anneal.str');

	}

	public function extractFinalValues()
	{
		$this->append('extract name ="------$\'vtAdjustDose\'--------"');

		$this->append('extract name="nxj" xj silicon mat.occno=1 x.val=0.1 junc.occno=1');

		$this->comment('extract the N++ regions sheet resistance');
		$this->append('extract name="n++ sheet rho" sheet.res material="Silicon" mat.occno=1 x.val=0.05 region.occno=1');

		$this->comment('extract the surface conc under the channel.');
		$this->append('extract name="chan surf conc" surf.conc impurity="Net Doping" material="Silicon" mat.occno=1 x.val=1.1');

		$this->comment('extract a curve of conductance versus bias');
		$this->append('extract start material="Polysilicon" mat.occno=1 bias=0.0 bias.step=0.2 bias.stop=2 x.val=0.45');
		$this->append('extract done name="sheet cond v bias" curve(bias,1dn.conduct material="Silicon" mat.occno=1  region.occno=1) outfile="extract_$\'vtAdjustDose\'.dat"');

		$this->comment('extract the long chan Vt');
		$this->append('extract name="n1dvt" 1dvt ntype x.val=1.3');
		$this->endl();
	}

	public function sourceDrainContact()
	{
		$this->append('etch oxide left x=0.2');

		$this->append('deposit aluminum thick=0.75');
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step56_post_al_dep.str');

		$this->append('etch aluminum right x=0.25');
		$this->append('struct outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_step58_post_al_etch.str');
		$this->endl();
	}

	public function finalizeDevice()
	{
		$this->append('structure mirror right');
		$this->endl();
		$this->append('electrode name=gate x=1.2 y=-0.2');
		$this->append('electrode name=source x=0.1 y=-0.5');
		$this->append('electrode name=drain x=2.65 y=-0.2');
		$this->append('electrode name=substrate backside');
		$this->endl();
		$this->append('structure outfile=./structures/'.$this->deviceName.'_$"vtAdjustDose"_electrical.str');
		$this->endl();
	}

	private function electricalSimulationWithDIBL()
	{
		$this->append('go atlas simflags="-P 1"');
		$this->append('OUTPUT CON.BAND');
		$this->endl();

		$this->comment('set material models');
		$this->append('models cvt srh print');
		$this->append("contact name=gate n.poly");
		$this->append("interface qf=3e10");
		$this->endl();

		$this->comment('get initial solution');
		 
		$this->append('solve init');

		$this->append('method newton trap');
		$this->append('solve prev');

		$this->comment('Bias the drain a bit...');
		$this->append("solve vdrain=0.025 vstep=0.025 vfinal=0.1 name=drain");
		$this->endl();

		$this->comment('Ramp the gate');
		$this->append('log outf=./logs/low_drain_bias_length_$"length".log master');
		if ($this->deviceName == 'nmos')
		{
			$this->append('solve vgate=0 vstep=0.01 vdrain=0.1 vfinal=2.5 name=gate cname=drain');
		}
		elseif ($this->deviceName == 'pmos')
		{
			$this->append('solve vgate=0 vstep=-0.01 vdrain=0.1 vfinal=-2.5 name=gate cname=drain');
		}
		
		$this->endl();

		$this->comment('extract device parameters');
		$this->append('extract name="vt1" (xintercept(maxslope(curve(abs(v."gate"),abs(i."drain")))) - abs(ave(v."drain"))/2.0)');
		$this->endl();

		$this->comment('now open a dummy log file...');
		$this->append('log off');
		$this->endl();

		$this->comment('Now start again and ramp the drain to 5 volts...');
		$this->append('solve init');
		$this->endl();

		$this->comment('Bias the drain to 5 volts......slowly at first....');
		$this->append("solve vdrain=0.025 vstep=0.025 vfinal=0.1 name=drain");
		$this->append("solve vdrain=0.1 vstep=0.1 vfinal=5 name=drain");
		$this->append("structure outfile=./structures/electrical_gate_off_$'length'.str");
		$this->endl();

		$this->comment('Ramp the gate again with another opened logfile...');
		$this->append('log outf=./logs/high_drain_bias_length_$"length".log master');
		if ($this->deviceName == 'nmos')
		{
			$this->append('solve vgate=0 vstep=0.01 vdrain=5 vfinal=2.5 name=gate cname=drain');
		}
		elseif ($this->deviceName == 'pmos')
		{
			$this->append('solve vgate=0 vstep=-0.01 vdrain=5 vfinal=-2.5 name=gate cname=drain');
		}
		
		$this->endl();

		$this->comment('extract the next device parameter with the drain now at 5 volts....');
		$this->append('extract name="vt2" (xintercept(maxslope(curve(abs(v."gate"),abs(i."drain")^.5))))');
		$this->endl();

		$this->append('log off');

		$this->comment('Calculate a DIBL parameter....in V/V');
		$this->append('extract name="ndibl" (($"vt1"-$"vt2")/(5.0-0.1))');
	}

	private function electricalSimulation()
	{
		$this->endl();
		$this->endl();
		$this->comment("Vt Atlas Test");
		$this->endl();

		$this->append('go atlas simflags="-P 1"');

		$this->comment("set material models");
		$this->append("models cvt srh print ");

		$this->append("contact name=gate n.poly");
		$this->append("interface qf=3e10");
		$this->append("method newton");

		$this->append("solve init");
		$this->endl();

		$this->comment("Bias the drain");
		$this->append("solve vdrain=0.1");
		
		$this->append('log outf=./logs/_$"vtAdjustDose"_vgs_id.log master');
		if ($this->deviceName == 'nmos')
		{
			$this->append("solve vgate=0 vstep=0.01 vfinal=3.0 name=gate");
		}
		else
		{
			$this->append("solve vgate=0 vstep=-0.01 vfinal=-3.0 name=gate");
		}
		
		$this->endl();

		$this->comment("extract device parameters");
		$this->append('extract name="nvt" (xintercept(maxslope(curve(abs(v."gate"),abs(i."drain")))) - abs(ave(v."drain"))/2.0)');
		$this->append('log off');
		$this->endl();
	}

	private function closeSimulator()
	{
		$this->append('quit');
	}

}

define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');



if ( ! function_exists('write_file'))
{
	function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE)
	{
		if ( ! $fp = @fopen($path, $mode))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);

		return TRUE;
	}
}

function main()
{
	$generator = new CmosProcess();
	$nmosUpToKoi = $generator->getNmosUpToKoi();
	$nmosPostKoi = $generator->getNmosPostKoi();

	$pmosUpToKoi = $generator->getPmosUpToKoi();
	$pmosPostKoi = $generator->getPmosPostKoi();

	$nmosLength = $generator->getAtlasCodeLengthDIBL('nmos');
	$pmosLength = $generator->getAtlasCodeLengthDIBL('pmos');

	write_file("./nmos_up_to_koi.in", $nmosUpToKoi);
	write_file("./nmos_post_koi.in", $nmosPostKoi);
	write_file("./nmos_dibl.in", $nmosLength);

	write_file("./pmos_up_to_koi.in", $pmosUpToKoi);
	write_file("./pmos_post_koi.in", $pmosPostKoi);
	write_file("./pmos_dibl.in", $pmosLength);
}

main();


?>
