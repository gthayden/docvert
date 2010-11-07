#!/bin/bash
source="$1"
destination="$2"
width="$3"
height="$4"
rsvg -w "${width}" -h "${height}" "${source}" "${destination}"
