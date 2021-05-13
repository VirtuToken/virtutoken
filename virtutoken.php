<!doctype html>
<title>Save and restore</title>

<script src="libv86.js"></script>
<script>
"use strict";

window.onload = function()
{
    var emulator = new V86Starter({
        wasm_path: "v86.wasm",
        memory_size: 32 * 1024 * 1024,
        vga_memory_size: 2 * 1024 * 1024,
        screen_container: document.getElementById("screen_container"),
        bios: {
            url: "../bios/seabios.bin",
        },
        vga_bios: {
            url: "../bios/vgabios.bin",
        },
        cdrom: {
            url: "http://www.mirrorservice.org/sites/clearfoundation.com/7/iso/x86_64/ClearOS-DVD-x86_64.iso",
        },
        autostart: true,
    });

    var state;

    document.getElementById("save_restore").onclick = function()
    {
        var button = this;

        if(state)
        {
            button.value = "Save state";

            emulator.restore_state(state);
            state = undefined;
        }
        else
        {
            emulator.save_state(function(error, new_state)
            {
                if(error)
                {
                    throw error;
                }

                console.log("Saved state of " + new_state.byteLength + " bytes");
                button.value = "Restore state";
                state = new_state;
            });
        }

        button.blur();
    };

    document.getElementById("save_file").onclick = function()
    {
        emulator.save_state(function(error, new_state)
        {
            if(error)
            {
                throw error;
            }

            var a = document.createElement("a");
            a.download = "v86state.bin";
            a.href = window.URL.createObjectURL(new Blob([new_state]));
            a.dataset.downloadurl = "application/octet-stream:" + a.download + ":" + a.href;
            a.click();
        });

        this.blur();
    };

    document.getElementById("restore_file").onchange = function()
    {
        if(this.files.length)
        {
            var filereader = new FileReader();
            emulator.stop();

            filereader.onload = function(e)
            {
                emulator.restore_state(e.target.result);
                emulator.run();
            };

            filereader.readAsArrayBuffer(this.files[0]);

            this.value = "";
        }

        this.blur();
    };
};
</script>

<input id="save_restore" type="button" value="Save state">
<input id="save_file" type="button" value="Save state to file">
Restore from file: <input id="restore_file" type="file">
<hr>

<!-- A minimal structure for the ScreenAdapter defined in browser/screen.js -->
<div id="screen_container">
    <div style="white-space: pre; font: 14px monospace; line-height: 14px"></div>
    <canvas style="display: none"></canvas>
</div>
