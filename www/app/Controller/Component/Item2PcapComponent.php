<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */
class Item2PcapComponent extends Component {
    var $someVar = null;
    var $controller = true;
    
    function doPcap($out_pcap, $item) {
        // empty file... avoid error
        $fp = fopen($out_pcap, 'aw');
        fclose($fp);
        $filtr = null;
        if (strstr ($item['ip_src'], ':') === false) { // ipv4
            if ($item['l4prot'] == 'TCP')
                $filtr .= '(ip.addr=='.$item['ip_src'].' and ip.addr=='.$item['ip_dst'].' and tcp.port=='.$item['port_src'].' and tcp.port=='.$item['port_dst'].')';
            else if ($item['l4prot'] == 'UDP')
                $filtr .= '(ip.addr=='.$item['ip_src'].' and ip.addr=='.$item['ip_dst'].' and udp.port=='.$item['port_src'].' and udp.port=='.$item['port_dst'].')';
            else
                $filtr .= '(ip.addr=='.$item['ip_src'].' and ip.addr=='.$item['ip_dst'].')';
        }
        else { // ipv6
            if ($item['l4prot'] == 'TCP')
                $filtr .= '(ipv6.addr=='.$item['ip_src'].' and ipv6.addr=='.$item['ip_dst'].' and tcp.port=='.$item['port_src'].' and tcp.port=='.$item['port_dst'].')';
            else if ($item['l4prot'] == 'UDP')
                $filtr .= '(ipv6.addr=='.$item['ip_src'].' and ipv6.addr=='.$item['ip_dst'].' and udp.port=='.$item['port_src'].' and udp.port=='.$item['port_dst'].')';
            else
                $filtr .= '(ipv6.addr=='.$item['ip_src'].' and ipv6.addr=='.$item['ip_dst'].')';
        }
        $fnum = sprintf("%08s", $item['capfile_id']);
        $pcap_file = Configure::read('Dataset.root').'/ds_'.$item['dataset_id'].'/raw/'.$fnum;
        if (file_exists($pcap_file)) {
            $cmd = "tshark -r ".$pcap_file." -Y \"".$filtr."\" -F libpcap -w ".$out_pcap;
            system($cmd);
        }
        else {
            die();
        }
    }
}
