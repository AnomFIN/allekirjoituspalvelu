import { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';

const MOCK_SIGN_DATA = {
  token: 'demo-token-123',
  document: 'Työsopimus_2025.pdf',
  sender: 'HR-osasto',
  senderEmail: 'hr@esimerkki.fi',
  sentAt: '24.4.2025',
  message: 'Hei,\n\nOlen lähettänyt sinulle työsopimuksen allekirjoitettavaksi. Klikkaa "Allekirjoita" -painiketta hyväksyäksesi sopimuksen ehdot.\n\nYstävällisin terveisin,\nHR-osasto',
};

const BANKS = [
  { id: 'op', name: 'OP', color: '#FF6600', bg: '#FFF3E0', abbr: 'OP' },
  { id: 'nordea', name: 'Nordea', color: '#0000A0', bg: '#E8E8FF', abbr: 'N' },
  { id: 'danske', name: 'Danske Bank', color: '#003755', bg: '#E0EAF0', abbr: 'DB' },
  { id: 'spankki', name: 'S-Pankki', color: '#00A651', bg: '#E0F7EA', abbr: 'S' },
  { id: 'aktia', name: 'Aktia', color: '#005AA7', bg: '#E0EDFF', abbr: 'A' },
  { id: 'pop', name: 'POP Pankki', color: '#E30613', bg: '#FFE8E8', abbr: 'POP' },
  { id: 'omasp', name: 'OmaSP', color: '#003882', bg: '#E0E8F7', abbr: 'Oma' },
  { id: 'aland', name: 'Ålandsbanken', color: '#004A99', bg: '#E0ECFF', abbr: 'Å' },
];

export default function SignPage() {
  useParams(); // token used for routing; actual API lookup to be implemented
  const navigate = useNavigate();
  const [step, setStep] = useState('review'); // 'review' | 'auth' | 'sign' | 'done' | 'rejected'
  const [selectedBank, setSelectedBank] = useState(null);
  const [showReject, setShowReject] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [agreed, setAgreed] = useState(false);

  const data = MOCK_SIGN_DATA;

  if (step === 'done') {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-2xl border border-slate-200 p-8 max-w-md w-full text-center shadow-sm">
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg className="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 className="text-2xl font-bold text-slate-900 mb-2">Allekirjoitus onnistui!</h1>
          <p className="text-slate-500 text-sm mb-6">
            Olet allekirjoittanut dokumentin <strong>{data.document}</strong> onnistuneesti.
            Saat vahvistuksen sähköpostiisi.
          </p>
          <div className="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-left mb-6">
            <p className="text-xs font-semibold text-green-700 uppercase tracking-wide mb-1">Allekirjoitustiedot</p>
            <p className="text-sm text-green-800">Päivämäärä: {new Date().toLocaleDateString('fi-FI')}</p>
            <p className="text-sm text-green-800">Tunnistautuminen: {selectedBank ? BANKS.find(b => b.id === selectedBank)?.name : 'Pankkitunnistautuminen'}</p>
          </div>
          <button
            onClick={() => navigate('/')}
            className="w-full bg-blue-900 text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition-colors"
          >
            Sulje
          </button>
        </div>
      </div>
    );
  }

  if (step === 'rejected') {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-2xl border border-slate-200 p-8 max-w-md w-full text-center shadow-sm">
          <div className="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg className="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <h1 className="text-2xl font-bold text-slate-900 mb-2">Allekirjoitus hylätty</h1>
          <p className="text-slate-500 text-sm mb-6">
            Olet hylännyt allekirjoituspyynnön. Lähettäjälle ilmoitetaan hylkäyksestä.
          </p>
          <button
            onClick={() => navigate('/')}
            className="w-full bg-slate-200 text-slate-700 py-3 rounded-xl font-semibold hover:bg-slate-300 transition-colors"
          >
            Sulje
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 py-8 px-4">
      <div className="max-w-2xl mx-auto space-y-5">
        {/* Header */}
        <div className="text-center">
          <div className="flex items-center justify-center gap-2 mb-3">
            <div className="w-8 h-8 bg-blue-900 rounded-lg flex items-center justify-center">
              <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </div>
            <span className="font-semibold text-blue-900 text-lg">Allekirjoituspalvelu</span>
          </div>
          <h1 className="text-xl font-bold text-slate-900">Allekirjoituspyyntö</h1>
          <p className="text-slate-500 text-sm mt-1">
            <strong>{data.sender}</strong> pyytää sinua allekirjoittamaan dokumentin
          </p>
        </div>

        {/* Document card */}
        <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
          <div className="flex items-center gap-4 mb-4">
            <div className="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg className="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h2 className="font-semibold text-slate-900">{data.document}</h2>
              <p className="text-xs text-slate-400">Lähettäjä: {data.sender} &lt;{data.senderEmail}&gt;</p>
              <p className="text-xs text-slate-400">Lähetetty: {data.sentAt}</p>
            </div>
          </div>

          {/* Message */}
          <div className="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 mb-4">
            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Viesti</p>
            <p className="text-sm text-slate-700 whitespace-pre-wrap">{data.message}</p>
          </div>

          {/* Mock PDF preview */}
          <div className="bg-slate-100 border border-slate-200 rounded-xl h-48 flex flex-col items-center justify-center gap-2 text-slate-400">
            <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p className="text-sm">PDF-esikatselu</p>
            <p className="text-xs">{data.document}</p>
          </div>
        </div>

        {/* Bank Auth section */}
        {(step === 'review' || step === 'auth') && (
          <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div className="flex items-center gap-2.5 mb-1">
              <svg className="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <h2 className="text-base font-semibold text-slate-900">Tunnistaudu pankkitunnuksilla</h2>
            </div>
            <p className="text-xs text-slate-500 mb-4">
              Allekirjoittaminen edellyttää vahvaa tunnistautumista. Valitse pankkisi ja kirjaudu sisään.
            </p>

            {/* Coming soon notice */}
            <div className="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 mb-4 flex items-start gap-2">
              <svg className="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p className="text-xs text-amber-700">
                <strong>Demo-tila:</strong> Varsinainen pankkitunnistautumisintegraatio toteutetaan myöhemmin. 
                Tässä demossa voit simuloida tunnistautumista painamalla mitä tahansa pankin painiketta.
              </p>
            </div>

            {/* Bank buttons */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              {BANKS.map((bank) => (
                <button
                  key={bank.id}
                  onClick={() => {
                    setSelectedBank(bank.id);
                    setStep('sign');
                  }}
                  className={`flex flex-col items-center gap-1.5 px-3 py-3.5 rounded-xl border-2 transition-all hover:shadow-md ${
                    selectedBank === bank.id
                      ? 'border-blue-500 shadow-md'
                      : 'border-slate-200 hover:border-slate-300'
                  }`}
                  style={{ backgroundColor: bank.bg }}
                >
                  <div
                    className="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm"
                    style={{ backgroundColor: bank.color }}
                  >
                    {bank.abbr}
                  </div>
                  <span className="text-xs font-medium text-slate-700 text-center leading-tight">{bank.name}</span>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Sign section – after bank selected */}
        {step === 'sign' && selectedBank && (
          <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <div className="flex items-center gap-3">
              <div
                className="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-sm"
                style={{ backgroundColor: BANKS.find(b => b.id === selectedBank)?.color }}
              >
                {BANKS.find(b => b.id === selectedBank)?.abbr}
              </div>
              <div>
                <p className="text-sm font-semibold text-slate-900">
                  {BANKS.find(b => b.id === selectedBank)?.name} – Tunnistautuminen
                </p>
                <p className="text-xs text-green-600 flex items-center gap-1">
                  <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                  </svg>
                  Tunnistautuminen onnistui (demo)
                </p>
              </div>
              <button
                onClick={() => { setSelectedBank(null); setStep('review'); }}
                className="ml-auto text-xs text-slate-400 hover:text-slate-600 underline"
              >
                Vaihda pankki
              </button>
            </div>

            {/* Agreement checkbox */}
            <label className="flex items-start gap-3 cursor-pointer group">
              <div className="mt-0.5 flex-shrink-0">
                <input
                  type="checkbox"
                  checked={agreed}
                  onChange={(e) => setAgreed(e.target.checked)}
                  className="w-4 h-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500"
                />
              </div>
              <span className="text-sm text-slate-700">
                Vahvistan, että olen lukenut dokumentin ja hyväksyn sen sisällön. Ymmärrän, että sähköinen allekirjoitus on oikeudellisesti sitova.
              </span>
            </label>

            <div className="flex gap-3">
              <button
                onClick={() => setStep('done')}
                disabled={!agreed}
                className={`flex-1 py-3 rounded-xl font-semibold text-sm transition-colors ${
                  agreed
                    ? 'bg-green-600 text-white hover:bg-green-700'
                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                }`}
              >
                ✍️ Allekirjoita nyt
              </button>
              <button
                onClick={() => setShowReject(true)}
                className="px-4 py-3 rounded-xl border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition-colors"
              >
                Hylkää
              </button>
            </div>
          </div>
        )}

        {/* Reject modal */}
        {showReject && (
          <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
              <h3 className="text-lg font-bold text-slate-900 mb-2">Hylkää allekirjoitus</h3>
              <p className="text-sm text-slate-500 mb-4">Voit lisätä syyn hylkäykselle (valinnainen).</p>
              <textarea
                rows={3}
                placeholder="Syy hylkäykselle..."
                value={rejectReason}
                onChange={(e) => setRejectReason(e.target.value)}
                className="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 resize-none mb-4"
              />
              <div className="flex gap-3">
                <button
                  onClick={() => {
                    setShowReject(false);
                    setStep('rejected');
                  }}
                  className="flex-1 bg-red-600 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-red-700 transition-colors"
                >
                  Vahvista hylkäys
                </button>
                <button
                  onClick={() => setShowReject(false)}
                  className="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-lg font-semibold text-sm hover:bg-slate-50 transition-colors"
                >
                  Peruuta
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
